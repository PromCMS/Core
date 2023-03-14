<?php

namespace PromCMS\Core\Database;

use PromCMS\Core\Exceptions\EntityDuplicateException;
use PromCMS\Core\Exceptions\EntityNotFoundException;
use Exception;
use ParseError;
use SleekDB\Cache;
use SleekDB\Store;
use SleekDB\Query as SleekQuery;

class Query
{
  use \PromCMS\Core\Database\Traits\Query\Managers, \PromCMS\Core\Database\Traits\Query\Builder;

  protected Store $store;
  /**  
   * @var Model|SingletonModel
   */
  protected $modelClass;
  protected string $currentLanguage;
  protected string $defaultLanguage;
  static string $TRANSLATIONS_FIELD_NAME = '_translations';
  static string $SINGLETON_NAME_FIELD_NAME = '_name';

  function __construct(Store $store, $modelClass, $defaultLanguage)
  {
    $this->store = $store;
    $this->modelClass = $modelClass;
    $this->currentLanguage = $defaultLanguage;
    $this->defaultLanguage = $defaultLanguage;
  }

  /**
   * Set language for current query
   */
  public function setLanguage(string $nextLanguage)
  {
    $this->currentLanguage = $nextLanguage;

    return $this;
  }

  /**
   * Create item
   */
  function create($payload)
  {
    // We add timestamps if current model has timestamps enabled
    if ($this->modelClass->hasTimestamps()) {
      $timeNow = new \DateTime();
      $payload['created_at'] = $timeNow->getTimestamp();
      $payload['updated_at'] = $timeNow->getTimestamp();
    }

    // Trigger events
    $payload = $this->modelClass::beforeSafe($payload);
    $payload = $this->modelClass::beforeCreate($payload);

    // Check for conflicts
    if ($conflictedFields = static::existsAgainstPayload($payload)) {
      throw new EntityDuplicateException(
        'This item already exists',
        $conflictedFields,
      );
    }

    [
      $neutralFields,
      $intlFields,
    ] = $this->modelClass->getInternationalizedFields();
    if ($this->modelClass->hasTranslationsEnabled()) {
      $formattedPayload = [];

      foreach ($neutralFields as $fieldName) {
        if (!isset($payload[$fieldName])) {
          continue;
        }
        $formattedPayload[$fieldName] = $payload[$fieldName];
      }

      foreach ($intlFields as $fieldName) {
        if (!isset($payload[$fieldName])) {
          continue;
        }
        if (!isset($formattedPayload[static::$TRANSLATIONS_FIELD_NAME])) {
          $formattedPayload[static::$TRANSLATIONS_FIELD_NAME] = [];
          $formattedPayload[static::$TRANSLATIONS_FIELD_NAME][$this->currentLanguage] = [];
        }
        $formattedPayload[static::$TRANSLATIONS_FIELD_NAME][$this->currentLanguage][$fieldName] = $payload[$fieldName];
      }

      $payload = $formattedPayload;
    }

    // Insert new item
    $result = $this->store->insert($payload);

    // Unwrap those translations
    if ($this->modelClass->hasTranslationsEnabled() && isset($result[static::$TRANSLATIONS_FIELD_NAME])) {
      $translations =
        $result[static::$TRANSLATIONS_FIELD_NAME][$this->currentLanguage];
      foreach ($intlFields as $fieldName) {
        if (!isset($translations[$fieldName])) {
          continue;
        }
        $result[$fieldName] = $translations[$fieldName];
      }

      unset($result[static::$TRANSLATIONS_FIELD_NAME]);
    }

    // Apply casts
    $result = static::applyCasts($result);

    // Convert it to model result
    $result = new ModelResult($this->modelClass, $result);

    // Trigger events
    $result = $this->modelClass::afterCreate($result);

    // Delete cache when item is created
    $this->getQueryCache()->deleteAll();

    return $result;
  }

  /**
   * Get many items that matches where clause
   */
  public function getMany(): array
  {
    $result = $this->getQueryBuilder()
      ->getQuery()
      ->fetch();

    $this->destroyQueryBuilder();

    return array_map(function ($item) {
      return static::applyCasts($item);
    }, $result);
  }

  /**
   * Shorthand for getOne
   */
  public function getOneById($id)
  {
    $this->getQueryBuilder()->where(['id', '=', static::serializeId($id)]);

    return $this->getOne();
  }

  /**
   * Get one item that matches where clause first
   */
  public function getOne()
  {
    $result = $this->getQueryBuilder()
      ->getQuery()
      ->first();

    $this->destroyQueryBuilder();

    if (count($result) === 0) {
      throw new EntityNotFoundException('Entity is missing');
    }

    return new ModelResult($this->modelClass, static::applyCasts($result));
  }

  function updateById($id, $payload)
  {
    $this->getQueryBuilder()->where(['id', '=', static::serializeId($id)]);

    return $this->update($payload);
  }

  /**
   * Update item(s), previously defined where conditions are applied too
   */
  function update($payload)
  {
    // If has timestamps enabled we attach updated_at
    if ($this->modelClass->hasTimestamps()) {
      $timeNow = new \DateTime();
      $payload['updated_at'] = $timeNow->getTimestamp();
    }

    $whereConditions = json_encode(
      $this->getQueryBuilder()->_getConditionProperties()['whereConditions'],
    );

    // If its just a regular model we need to check if id is set before we run update
    if (($this->modelClass instanceof SingletonModel) === FALSE) {
      if (!strpos($whereConditions, '["id","=",') !== false) {
        throw new Exception('You must specify where clause to use update');
      }

      // Hacky way to access the id from where
      $id = intval(explode(']', explode('["id","=",', $whereConditions)[1])[0]);

      // Check for conflicts
      if ($conflictedFields = $this->existsAgainstPayload($payload, $id)) {
        throw new EntityDuplicateException(
          'This item already exists',
          $conflictedFields,
        );
      }
    }


    // Trigger events
    $payload = $this->modelClass::beforeSafe($payload);
    $payload = $this->payloadFieldKeysToTranslations($payload);

    // Update item in store and get it back
    $result = $this->getQueryBuilder()
      ->getQuery()
      ->update($payload, true);

    // Purge cache for current store - to ensure getMany() results are valid too
    if ($result) {
      $this->getQueryCache()->deleteAll();
    }

    // Destroy query builder that was made for this instance
    $this->destroyQueryBuilder();

    // If there are no results we fire exception
    if ($result === false) {
      throw new EntityNotFoundException();
    }

    $result = $result[0];

    // Apply aliases to place translations to root and remove translation field
    if ($this->modelClass->hasTranslationsEnabled() && isset($result[static::$TRANSLATIONS_FIELD_NAME])) {
      $newResult = [];

      foreach ($this->getI18nFieldKeyAliases() as $fieldKey => $fieldValueOrKey) {
        if (is_int($fieldKey)) {
          if (isset($result[$fieldValueOrKey])) {
            $newResult[$fieldValueOrKey] = $result[$fieldValueOrKey];
          }

          continue;
        }

        $newResult[$fieldKey] = $fieldValueOrKey($result);
      }

      $result = $newResult;
    }

    // We apply casts and return it via ModelResult
    return new ModelResult($this->modelClass, static::applyCasts($result));
  }

  /**
   * Shorthand for delete, accepts previously defined where conditions
   */
  function deleteById($id)
  {
    $this->getQueryBuilder()->where(['id', '=', static::serializeId($id)]);

    return $this->delete();
  }

  /**
   * Delete item
   */
  function delete()
  {
    $result = $this->getQueryBuilder()
      ->getQuery()
      ->delete(SleekQuery::DELETE_RETURN_RESULTS);

    if ($result == false) {
      return $result;
    }

    // Purge cache for current store - to ensure getMany() results are valid too
    $this->getQueryCache()->deleteAll();

    return new ModelResult($this->modelClass, static::applyCasts($result[0]));
  }

  /**
   * Much more efficient method to check if some entry exists. Does not attach to current query builder
   */
  function exists($where)
  {
    if (!count($where)) {
      return false;
    }

    $this->getStore()
      ->createQueryBuilder()
      ->where($where)
      ->getQuery()
      ->exists();
  }

  // PRIVATE FUNCTIONS

  private static function serializeId($id): int
  {
    $intId = intval($id);
    if ($intId === 0) {
      if ($id === '0') {
        throw new Exception("Cannot query item with id '0'");
      } else {
        throw new ParseError("'$id' cannot be converted to number");
      }
    }

    return $intId;
  }

  private function applyCasts($args)
  {
    $casts = $this->modelClass::getCasts();
    if (count($casts) == 0) {
      return $args;
    }

    foreach ($casts as $castFieldName => $castTo) {
      if (!isset($args[$castFieldName])) {
        continue;
      }

      $fieldValue = $args[$castFieldName];
      switch ($castTo) {
        case 'json':
          $casted = json_encode($fieldValue);
          break;
        case 'array':
          $casted = is_array($fieldValue)
            ? $fieldValue
            : json_decode($fieldValue);
          break;
        default:
          throw new \Exception(
            "Unknown castTo $castTo on field $castFieldName",
          );
          break;
      }
      $args[$castFieldName] = $casted;
    }

    return $args;
  }

  protected function existsAgainstPayload($payload, $ignoreId = null)
  {
    $uniqueFilter = [];
    $index = 1;
    $translationsFieldName = static::$TRANSLATIONS_FIELD_NAME;
    $currentLanguageKey = $this->currentLanguage;
    $intlFields = $this->modelClass->getInternationalizedFields()[1];
    $filledUniqueFields = array_filter(
      $this->modelClass::getUniqueFields(),
      function ($item) use ($payload) {
        return isset($payload[$item]);
      },
    );

    foreach ($filledUniqueFields as $uniqueFieldName) {
      $uniqueFilter[] =
        $this->modelClass->hasTranslationsEnabled() &&
        in_array($uniqueFieldName, $intlFields)
        ? [
          "$translationsFieldName.$currentLanguageKey.$uniqueFieldName",
          '=',
          $payload[$uniqueFieldName],
        ]
        : [$uniqueFieldName, '=', $payload[$uniqueFieldName]];

      if (count($filledUniqueFields) !== $index) {
        $uniqueFilter[] = 'OR';
      }
      $index++;
    }

    if (!count($uniqueFilter)) {
      return false;
    }

    try {
      $match = $this->getStore()
        ->createQueryBuilder()
        ->select($this->getI18nFieldKeyAliases())
        ->where(
          $ignoreId !== null
            ? [$uniqueFilter, 'AND', ['id', '!=', $ignoreId]]
            : $uniqueFilter,
        )
        ->getQuery()
        ->first();

      if (count($match) == 0) {
        throw new EntityNotFoundException();
      }

      $conflictedFields = [];

      foreach ($filledUniqueFields as $uniqueFieldName) {
        if ($match[$uniqueFieldName] == $payload[$uniqueFieldName]) {
          $conflictedFields[] = $uniqueFieldName;
        }
      }

      return $conflictedFields;
    } catch (\Exception $exception) {
      if ($exception instanceof EntityNotFoundException) {
        return false;
      }

      throw $exception;
    }
  }

  protected function payloadFieldKeysToTranslations($payload)
  {
    if (!$this->modelClass->hasTranslationsEnabled()) {
      return $payload;
    }

    $intlFields = $this->modelClass->getInternationalizedFields()[1];
    $translationsFieldName = static::$TRANSLATIONS_FIELD_NAME;
    $currentLanguageKey = $this->currentLanguage;

    foreach ($intlFields as $fieldName) {
      if (!isset($payload[$fieldName])) {
        continue;
      }

      $payload["$translationsFieldName.$currentLanguageKey.$fieldName"] =
        $payload[$fieldName];
      unset($payload[$fieldName]);
    }

    return $payload;
  }

  protected function getQueryCache(): Cache
  {
    return $this->getQueryBuilder()
      ->getQuery()
      ->getCache();
  }

  protected function getI18nFieldKeyAliases()
  {
    if (!$this->modelClass->hasTranslationsEnabled()) {
      return $this->modelClass->getFieldKeys();
    }

    [
      $neutralFields,
      $intlFields,
    ] = $this->modelClass->getInternationalizedFields();

    $fieldKeys = $neutralFields;
    $translationsFieldName = static::$TRANSLATIONS_FIELD_NAME;
    $currentLanguageKey = $this->currentLanguage;
    $defaultLanguage = $this->defaultLanguage;

    // This goes through each field and sets filter function which virtually normalizes translated field from database to normal response
    foreach ($intlFields as $fieldName) {
      $fieldKeys[$fieldName] = function ($item) use (
        $fieldName,
        $translationsFieldName,
        $currentLanguageKey,
        $defaultLanguage
      ) {
        // If there are no translations then return void
        if (!isset($item[$translationsFieldName])) {
          return;
        }

        $itemTranslations = $item[$translationsFieldName];

        if (isset($itemTranslations[$currentLanguageKey][$fieldName])) {
          return $itemTranslations[$currentLanguageKey][$fieldName];
        } elseif (isset($itemTranslations[$defaultLanguage][$fieldName])) {
          return $itemTranslations[$defaultLanguage][$fieldName];
        }
      };
    }

    return $fieldKeys;
  }
}
