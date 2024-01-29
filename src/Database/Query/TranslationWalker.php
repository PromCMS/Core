<?php

namespace PromCMS\Core\Database\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\AST\FromClause;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\AST\RangeVariableDeclaration;
use Doctrine\ORM\Query\AST\SelectStatement;
use Doctrine\ORM\Query\AST\SubselectFromClause;
use Doctrine\ORM\Query\Exec\SingleSelectExecutor;
use Doctrine\ORM\Query\SqlWalker;

class TranslationWalker extends SqlWalker
{
  public const HINT_LOCALE = '__prom.translations.locale';


  /**
   * Stores all component references from select clause
   *
   * @var array<string, array<string, mixed>>
   *
   * @phpstan-var array<string, array{metadata: ClassMetadata}>
   */
  private array $translatedComponents = [];

  /**
   * DBAL database platform
   *
   * @var AbstractPlatform
   */
  private $platform;

  /**
   * DBAL database connection
   *
   * @var Connection
   */
  private $conn;

  /**
   * List of aliases to replace with translation
   * content reference
   *
   * @var array<string, string>
   */
  private array $replacements = [];

  /**
   * List of joins for translated components in query
   *
   * @var array<string, string>
   */
  private array $components = [];

  public function __construct($query, $parserResult, array $queryComponents)
  {
    parent::__construct($query, $parserResult, $queryComponents);
    $this->conn = $this->getConnection();
    $this->platform = $this->getConnection()->getDatabasePlatform();
    $this->extractTranslatedComponents($queryComponents);
  }

  /**
   * @return Query\Exec\AbstractSqlExecutor
   */
  public function getExecutor($AST)
  {
    // If it's not a Select, the TreeWalker ought to skip it, and just return the parent.
    // @see https://github.com/Atlantic18/DoctrineExtensions/issues/2013
    if (!$AST instanceof SelectStatement) {
      return parent::getExecutor($AST);
    }
    $this->prepareTranslatedComponents();

    return new SingleSelectExecutor($AST, $this);
  }

  /**
   * @return string
   */
  public function walkSelectClause($selectClause)
  {
    $result = parent::walkSelectClause($selectClause);

    return $this->replace($this->replacements, $result);
  }

  /**
   * @return string
   */
  public function walkFromClause($fromClause)
  {
    $result = parent::walkFromClause($fromClause);
    $result .= $this->joinTranslations($fromClause);

    return $result;
  }

  /**
   * @return string
   */
  public function walkWhereClause($whereClause)
  {
    $result = parent::walkWhereClause($whereClause);

    return $this->replace($this->replacements, $result);
  }

  /**
   * @return string
   */
  public function walkHavingClause($havingClause)
  {
    $result = parent::walkHavingClause($havingClause);

    return $this->replace($this->replacements, $result);
  }

  /**
   * @return string
   */
  public function walkOrderByClause($orderByClause)
  {
    $result = parent::walkOrderByClause($orderByClause);

    return $this->replace($this->replacements, $result);
  }

  /**
   * @return string
   */
  public function walkSubselect($subselect)
  {
    return parent::walkSubselect($subselect);
  }

  /**
   * @return string
   */
  public function walkSubselectFromClause($subselectFromClause)
  {
    $result = parent::walkSubselectFromClause($subselectFromClause);
    $result .= $this->joinTranslations($subselectFromClause);

    return $result;
  }

  /**
   * @return string
   */
  public function walkSimpleSelectClause($simpleSelectClause)
  {
    $result = parent::walkSimpleSelectClause($simpleSelectClause);

    return $this->replace($this->replacements, $result);
  }

  /**
   * @return string
   */
  public function walkGroupByClause($groupByClause)
  {
    $result = parent::walkGroupByClause($groupByClause);

    return $this->replace($this->replacements, $result);
  }

  /**
   * Walks from clause, and creates translation joins
   * for the translated components
   *
   * @param FromClause|SubselectFromClause $from
   */
  private function joinTranslations(Node $from): string
  {
    $result = '';
    foreach ($from->identificationVariableDeclarations as $decl) {
      if ($decl->rangeVariableDeclaration instanceof RangeVariableDeclaration) {
        if (isset($this->components[$decl->rangeVariableDeclaration->aliasIdentificationVariable])) {
          $result .= $this->components[$decl->rangeVariableDeclaration->aliasIdentificationVariable];
        }
      }
      if (isset($decl->joinVariableDeclarations)) {
        foreach ($decl->joinVariableDeclarations as $joinDecl) {
          if ($joinDecl->join instanceof Join) {
            if (isset($this->components[$joinDecl->join->aliasIdentificationVariable])) {
              $result .= $this->components[$joinDecl->join->aliasIdentificationVariable];
            }
          }
        }
      } else {
        // based on new changes
        foreach ($decl->joins as $join) {
          if ($join instanceof Join) {
            if (isset($this->components[$join->joinAssociationDeclaration->aliasIdentificationVariable])) {
              $result .= $this->components[$join->joinAssociationDeclaration->aliasIdentificationVariable];
            }
          }
        }
      }
    }

    return $result;
  }

  /**
   * Creates a left join list for translations
   * on used query components
   *
   * @todo: make it cleaner
   */
  private function prepareTranslatedComponents(): void
  {
    $q = $this->getQuery();
    $locale = $q->getHint(TranslationWalker::HINT_LOCALE);
    if (!$locale) {
      // Locale was not provided so no work to be done
      return;
    }
    $em = $this->getEntityManager();
    $quoteStrategy = $em->getConfiguration()->getQuoteStrategy();
    $joinStrategy = 'LEFT';

    foreach ($this->translatedComponents as $dqlAlias => $comp) {
      /** @var ClassMetadata $meta */
      $meta = $comp['metadata'];
      $transClass = $meta->getName() . 'Translation'; // TODO
      $transMeta = $em->getClassMetadata($transClass);
      $transTable = $quoteStrategy->getTableName($transMeta, $this->platform);
      $staticFields = ['id', 'locale', 'field'];

      // // Join...
      // $sql = " {$joinStrategy} JOIN " . $transTable . ' ' . $tblAlias;
      // // ON locale = $locale
      // $sql .= ' ON ' . $tblAlias . '.' . $quoteStrategy->getColumnName('locale', $transMeta, $this->platform)
      //   . ' = ' . $this->conn->quote($locale);
      // $identifier = $meta->getSingleIdentifierFieldName();
      // $idColName = $quoteStrategy->getColumnName($identifier, $meta, $this->platform);
      // // AND object_id = $idColName
      // $sql .= ' AND ' . $tblAlias . '.' . $transMeta->getSingleAssociationJoinColumnName('object')
      //   . ' = ' . $compTblAlias . '.' . $idColName;

      foreach ($transMeta->getFieldNames() as $field) {
        if (in_array($field, $staticFields)) {
          continue;
        }

        $compTblAlias = $this->walkIdentificationVariable($dqlAlias, $field);
        $tblAlias = $this->getSQLTableAlias('trans' . $compTblAlias . $field);
        $sql = " {$joinStrategy} JOIN " . $transTable . ' ' . $tblAlias;
        $sql .= ' ON ' . $tblAlias . '.' . $quoteStrategy->getColumnName('locale', $transMeta, $this->platform)
          . ' = ' . $this->conn->quote($locale);
        $identifier = $meta->getSingleIdentifierFieldName();
        $idColName = $quoteStrategy->getColumnName($identifier, $meta, $this->platform);
        $sql .= ' AND ' . $tblAlias . '.' . $transMeta->getSingleAssociationJoinColumnName('object')
          . ' = ' . $compTblAlias . '.' . $idColName;
        isset($this->components[$dqlAlias]) ? $this->components[$dqlAlias] .= $sql : $this->components[$dqlAlias] = $sql;

        $originalField = $compTblAlias . '.' . $quoteStrategy->getColumnName($field, $meta, $this->platform);
        $substituteField = $tblAlias . '.' . $quoteStrategy->getColumnName($field, $transMeta, $this->platform);

        $substituteField = 'COALESCE(' . $substituteField . ', ' . $originalField . ')';

        $this->replacements[$originalField] = $substituteField;
      }
    }
  }


  /**
   * Search for translated components in the select clause
   *
   * @param array<string, array<string, ClassMetadata>> $queryComponents
   *
   * @phpstan-param array<string, array{metadata: ClassMetadata}> $queryComponents
   */
  private function extractTranslatedComponents(array $queryComponents): void
  {
    $em = $this->getEntityManager();
    foreach ($queryComponents as $alias => $comp) {
      if (!isset($comp['metadata'])) {
        continue;
      }
      $classMeta = $comp['metadata'];

      // TODO - extract translation classname from prom attribute instead
      if (class_exists($classMeta->getReflectionClass()->getName() . 'Translation')) {
        $this->translatedComponents[$alias] = $comp;
      }

      // if ($config && isset($config['fields'])) {
      //   $this->translatedComponents[$alias] = $comp;
      // }
    }
  }

  /**
   * Replaces given sql $str with required
   * results
   *
   * @param array<string, string> $repl
   */
  private function replace(array $repl, string $str): string
  {
    foreach ($repl as $target => $result) {
      $str = preg_replace_callback('/(\s|\()(' . $target . ')(,?)(\s|\)|$)/smi', static fn(array $m): string => $m[1] . $result . $m[3] . $m[4], $str);
    }

    return $str;
  }

  /**
   * Casts a foreign key if needed
   *
   * @NOTE: personal translations manages that for themselves.
   *
   * @param string $component a column with an alias to cast
   * @param string $typeFK    translation table foreign key type
   * @param string $typePK    primary key type which references translation table
   *
   * @return string modified $component if needed
   */
  private function getCastedForeignKey(string $component, string $typeFK, string $typePK): string
  {
    // the keys are of same type
    if ($typeFK === $typePK) {
      return $component;
    }

    // try to look at postgres casting
    if ($this->platform instanceof PostgreSQLPlatform) {
      switch ($typeFK) {
        case 'string':
        case 'guid':
          // need to cast to VARCHAR
          $component .= '::VARCHAR';

          break;
      }
    }

    // @TODO may add the same thing for MySQL for performance to match index

    return $component;
  }
}
