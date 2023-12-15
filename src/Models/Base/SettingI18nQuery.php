<?php

namespace PromCMS\Core\Models\Base;

use \Exception;
use \PDO;
use PromCMS\Core\Models\SettingI18n as ChildSettingI18n;
use PromCMS\Core\Models\SettingI18nQuery as ChildSettingI18nQuery;
use PromCMS\Core\Models\Map\SettingI18nTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the `prom__settings_i18n` table.
 *
 * @method     ChildSettingI18nQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildSettingI18nQuery orderByLocale($order = Criteria::ASC) Order by the locale column
 * @method     ChildSettingI18nQuery orderByContent($order = Criteria::ASC) Order by the content column
 * @method     ChildSettingI18nQuery orderByName($order = Criteria::ASC) Order by the name column
 *
 * @method     ChildSettingI18nQuery groupById() Group by the id column
 * @method     ChildSettingI18nQuery groupByLocale() Group by the locale column
 * @method     ChildSettingI18nQuery groupByContent() Group by the content column
 * @method     ChildSettingI18nQuery groupByName() Group by the name column
 *
 * @method     ChildSettingI18nQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildSettingI18nQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildSettingI18nQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildSettingI18nQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildSettingI18nQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildSettingI18nQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildSettingI18nQuery leftJoinSetting($relationAlias = null) Adds a LEFT JOIN clause to the query using the Setting relation
 * @method     ChildSettingI18nQuery rightJoinSetting($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Setting relation
 * @method     ChildSettingI18nQuery innerJoinSetting($relationAlias = null) Adds a INNER JOIN clause to the query using the Setting relation
 *
 * @method     ChildSettingI18nQuery joinWithSetting($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Setting relation
 *
 * @method     ChildSettingI18nQuery leftJoinWithSetting() Adds a LEFT JOIN clause and with to the query using the Setting relation
 * @method     ChildSettingI18nQuery rightJoinWithSetting() Adds a RIGHT JOIN clause and with to the query using the Setting relation
 * @method     ChildSettingI18nQuery innerJoinWithSetting() Adds a INNER JOIN clause and with to the query using the Setting relation
 *
 * @method     \PromCMS\Core\Models\SettingQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildSettingI18n|null findOne(?ConnectionInterface $con = null) Return the first ChildSettingI18n matching the query
 * @method     ChildSettingI18n findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildSettingI18n matching the query, or a new ChildSettingI18n object populated from the query conditions when no match is found
 *
 * @method     ChildSettingI18n|null findOneById(int $id) Return the first ChildSettingI18n filtered by the id column
 * @method     ChildSettingI18n|null findOneByLocale(string $locale) Return the first ChildSettingI18n filtered by the locale column
 * @method     ChildSettingI18n|null findOneByContent(array $content) Return the first ChildSettingI18n filtered by the content column
 * @method     ChildSettingI18n|null findOneByName(string $name) Return the first ChildSettingI18n filtered by the name column
 *
 * @method     ChildSettingI18n requirePk($key, ?ConnectionInterface $con = null) Return the ChildSettingI18n by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSettingI18n requireOne(?ConnectionInterface $con = null) Return the first ChildSettingI18n matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildSettingI18n requireOneById(int $id) Return the first ChildSettingI18n filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSettingI18n requireOneByLocale(string $locale) Return the first ChildSettingI18n filtered by the locale column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSettingI18n requireOneByContent(array $content) Return the first ChildSettingI18n filtered by the content column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSettingI18n requireOneByName(string $name) Return the first ChildSettingI18n filtered by the name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildSettingI18n[]|Collection find(?ConnectionInterface $con = null) Return ChildSettingI18n objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildSettingI18n> find(?ConnectionInterface $con = null) Return ChildSettingI18n objects based on current ModelCriteria
 *
 * @method     ChildSettingI18n[]|Collection findById(int|array<int> $id) Return ChildSettingI18n objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildSettingI18n> findById(int|array<int> $id) Return ChildSettingI18n objects filtered by the id column
 * @method     ChildSettingI18n[]|Collection findByLocale(string|array<string> $locale) Return ChildSettingI18n objects filtered by the locale column
 * @psalm-method Collection&\Traversable<ChildSettingI18n> findByLocale(string|array<string> $locale) Return ChildSettingI18n objects filtered by the locale column
 * @method     ChildSettingI18n[]|Collection findByContent(array|array<array> $content) Return ChildSettingI18n objects filtered by the content column
 * @psalm-method Collection&\Traversable<ChildSettingI18n> findByContent(array|array<array> $content) Return ChildSettingI18n objects filtered by the content column
 * @method     ChildSettingI18n[]|Collection findByName(string|array<string> $name) Return ChildSettingI18n objects filtered by the name column
 * @psalm-method Collection&\Traversable<ChildSettingI18n> findByName(string|array<string> $name) Return ChildSettingI18n objects filtered by the name column
 *
 * @method     ChildSettingI18n[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildSettingI18n> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class SettingI18nQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \PromCMS\Core\Models\Base\SettingI18nQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'core', $modelName = '\\PromCMS\\Core\\Models\\SettingI18n', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildSettingI18nQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildSettingI18nQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildSettingI18nQuery) {
            return $criteria;
        }
        $query = new ChildSettingI18nQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj = $c->findPk(array(12, 34), $con);
     * </code>
     *
     * @param array[$id, $locale] $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildSettingI18n|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(SettingI18nTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = SettingI18nTableMap::getInstanceFromPool(serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]))))) {
            // the object is already in the instance pool
            return $obj;
        }

        return $this->findPkSimple($key, $con);
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con A connection object
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildSettingI18n A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, locale, content, name FROM prom__settings_i18n WHERE id = :p0 AND locale = :p1';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key[0], PDO::PARAM_INT);
            $stmt->bindValue(':p1', $key[1], PDO::PARAM_STR);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildSettingI18n $obj */
            $obj = new ChildSettingI18n();
            $obj->hydrate($row);
            SettingI18nTableMap::addInstanceToPool($obj, serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]));
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con A connection object
     *
     * @return ChildSettingI18n|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, ConnectionInterface $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(array(12, 56), array(832, 123), array(123, 456)), $con);
     * </code>
     * @param array $keys Primary keys to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return Collection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param mixed $key Primary key to use for the query
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        $this->addUsingAlias(SettingI18nTableMap::COL_ID, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(SettingI18nTableMap::COL_LOCALE, $key[1], Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param array|int $keys The list of primary key to use for the query
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        if (empty($keys)) {
            $this->add(null, '1<>1', Criteria::CUSTOM);

            return $this;
        }
        foreach ($keys as $key) {
            $cton0 = $this->getNewCriterion(SettingI18nTableMap::COL_ID, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(SettingI18nTableMap::COL_LOCALE, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $this->addOr($cton0);
        }

        return $this;
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @see       filterBySetting()
     *
     * @param mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterById($id = null, ?string $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(SettingI18nTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(SettingI18nTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(SettingI18nTableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the locale column
     *
     * Example usage:
     * <code>
     * $query->filterByLocale('fooValue');   // WHERE locale = 'fooValue'
     * $query->filterByLocale('%fooValue%', Criteria::LIKE); // WHERE locale LIKE '%fooValue%'
     * $query->filterByLocale(['foo', 'bar']); // WHERE locale IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $locale The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByLocale($locale = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($locale)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(SettingI18nTableMap::COL_LOCALE, $locale, $comparison);

        return $this;
    }

    /**
     * Filter the query on the content column
     *
     * @param array $content The values to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByContent($content = null, ?string $comparison = null)
    {
        $key = $this->getAliasedColName(SettingI18nTableMap::COL_CONTENT);
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            foreach ($content as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addAnd($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_SOME) {
            foreach ($content as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addOr($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            foreach ($content as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addAnd($key, $value, Criteria::NOT_LIKE);
                } else {
                    $this->add($key, $value, Criteria::NOT_LIKE);
                }
            }
            $this->addOr($key, null, Criteria::ISNULL);

            return $this;
        }

        $this->addUsingAlias(SettingI18nTableMap::COL_CONTENT, $content, $comparison);

        return $this;
    }

    /**
     * Filter the query on the name column
     *
     * Example usage:
     * <code>
     * $query->filterByName('fooValue');   // WHERE name = 'fooValue'
     * $query->filterByName('%fooValue%', Criteria::LIKE); // WHERE name LIKE '%fooValue%'
     * $query->filterByName(['foo', 'bar']); // WHERE name IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $name The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByName($name = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(SettingI18nTableMap::COL_NAME, $name, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \PromCMS\Core\Models\Setting object
     *
     * @param \PromCMS\Core\Models\Setting|ObjectCollection $setting The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterBySetting($setting, ?string $comparison = null)
    {
        if ($setting instanceof \PromCMS\Core\Models\Setting) {
            return $this
                ->addUsingAlias(SettingI18nTableMap::COL_ID, $setting->getId(), $comparison);
        } elseif ($setting instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(SettingI18nTableMap::COL_ID, $setting->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterBySetting() only accepts arguments of type \PromCMS\Core\Models\Setting or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Setting relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinSetting(?string $relationAlias = null, ?string $joinType = 'LEFT JOIN')
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Setting');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'Setting');
        }

        return $this;
    }

    /**
     * Use the Setting relation Setting object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \PromCMS\Core\Models\SettingQuery A secondary query class using the current class as primary query
     */
    public function useSettingQuery($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        return $this
            ->joinSetting($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Setting', '\PromCMS\Core\Models\SettingQuery');
    }

    /**
     * Use the Setting relation Setting object
     *
     * @param callable(\PromCMS\Core\Models\SettingQuery):\PromCMS\Core\Models\SettingQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withSettingQuery(
        callable $callable,
        string $relationAlias = null,
        ?string $joinType = 'LEFT JOIN'
    ) {
        $relatedQuery = $this->useSettingQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to Setting table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \PromCMS\Core\Models\SettingQuery The inner query object of the EXISTS statement
     */
    public function useSettingExistsQuery($modelAlias = null, $queryClass = null, $typeOfExists = 'EXISTS')
    {
        /** @var $q \PromCMS\Core\Models\SettingQuery */
        $q = $this->useExistsQuery('Setting', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to Setting table for a NOT EXISTS query.
     *
     * @see useSettingExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \PromCMS\Core\Models\SettingQuery The inner query object of the NOT EXISTS statement
     */
    public function useSettingNotExistsQuery($modelAlias = null, $queryClass = null)
    {
        /** @var $q \PromCMS\Core\Models\SettingQuery */
        $q = $this->useExistsQuery('Setting', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to Setting table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \PromCMS\Core\Models\SettingQuery The inner query object of the IN statement
     */
    public function useInSettingQuery($modelAlias = null, $queryClass = null, $typeOfIn = 'IN')
    {
        /** @var $q \PromCMS\Core\Models\SettingQuery */
        $q = $this->useInQuery('Setting', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to Setting table for a NOT IN query.
     *
     * @see useSettingInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \PromCMS\Core\Models\SettingQuery The inner query object of the NOT IN statement
     */
    public function useNotInSettingQuery($modelAlias = null, $queryClass = null)
    {
        /** @var $q \PromCMS\Core\Models\SettingQuery */
        $q = $this->useInQuery('Setting', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildSettingI18n $settingI18n Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($settingI18n = null)
    {
        if ($settingI18n) {
            $this->addCond('pruneCond0', $this->getAliasedColName(SettingI18nTableMap::COL_ID), $settingI18n->getId(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(SettingI18nTableMap::COL_LOCALE), $settingI18n->getLocale(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

    /**
     * Deletes all rows from the prom__settings_i18n table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(SettingI18nTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            SettingI18nTableMap::clearInstancePool();
            SettingI18nTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    /**
     * Performs a DELETE on the database based on the current ModelCriteria
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public function delete(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(SettingI18nTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(SettingI18nTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            SettingI18nTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            SettingI18nTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
