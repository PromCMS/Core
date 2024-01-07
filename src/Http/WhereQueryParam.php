<?php

namespace PromCMS\Core\Http;

use Doctrine\ORM\QueryBuilder;

class WhereQueryParam
{
  public $parsed = [];
  private static $searchParamsCriteriaToExpressionMethod = [
    '=' => 'eq',
    '>' => 'qt',
    '<' => 'lt',
    'LIKE' => 'like',
    'IN' => 'in',
    'NOT IN' => 'notIn'
  ];
  public function __construct(string|array $param)
  {
    $PART_SEPARATOR = ';';
    $PIECE_SEPARATOR = '.';
    $stringToExtract = $param;

    // If there is an array instead of string, happens when it was defined like this in url
    if (is_array($param)) {
      $stringToExtract = implode($PART_SEPARATOR, $param);
    }
    $allowedCriteria = array_keys(static::$searchParamsCriteriaToExpressionMethod);

    // Split by separator and attach each process
    foreach (explode($PART_SEPARATOR, $stringToExtract) as $part) {
      $pieces = explode($PIECE_SEPARATOR, $part);

      if (count($pieces) < 3) {
        continue;
      }

      [$fieldName, $criteria] = $pieces;
      // string in query could have dot and in that case we need to make sure that string includes dot back
      $value = implode($PIECE_SEPARATOR, array_slice($pieces, 2));

      if (!empty($fieldName) && !empty($criteria) && in_array($criteria, $allowedCriteria)) {
        if ($criteria === 'NOT IN' || $criteria === 'IN') {
          $this->parsed[$fieldName] = [
            'value' => explode(',', $value),
            'criteria' => $criteria
          ];

          continue;
        }

        $parsedValue = $value;

        if (is_numeric($value)) {
          $parsedValue = intval($value);
        }

        if ($value === 'true' || $value === 'false') {
          $parsedValue = $value === 'true';
        }

        $this->parsed[$fieldName] = [
          'value' => $parsedValue,
          'criteria' => $criteria
        ];
      }
    }
  }

  public function add(string $name, string $criteria, mixed $value)
  {
    $allowedCriteria = array_keys(static::$searchParamsCriteriaToExpressionMethod);
    if (!in_array($criteria, $allowedCriteria)) {
      return $this;
    }

    $this->parsed[$name] = [
      'criteria' => $criteria,
      'value' => $value
    ];

    return $this;
  }

  public function toQuery(QueryBuilder &$qb, string $for)
  {
    $conditions = [];

    foreach ($this->parsed as $fieldName => $entry) {
      $paramName = ":$fieldName";
      $criteria = static::$searchParamsCriteriaToExpressionMethod[$entry['criteria']];

      $conditions[] = $qb->expr()->{$criteria}("$for.$fieldName", $paramName);
      $qb->setParameter($paramName, $entry['value']);
    }

    return $qb->where($qb->expr()->andX(...$conditions));
  }
}