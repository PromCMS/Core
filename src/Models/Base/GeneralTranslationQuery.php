<?php

namespace PromCMS\Core\Models\Base;

use \Exception;
use \PDO;
use PromCMS\Core\Models\GeneralTranslation as ChildGeneralTranslation;
use PromCMS\Core\Models\GeneralTranslationQuery as ChildGeneralTranslationQuery;
use PromCMS\Core\Models\Map\GeneralTranslationTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the `prom__general_translations` table.
 *
 * @method     ChildGeneralTranslationQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildGeneralTranslationQuery orderByLang($order = Criteria::ASC) Order by the lang column
 * @method     ChildGeneralTranslationQuery orderByKey($order = Criteria::ASC) Order by the key column
 * @method     ChildGeneralTranslationQuery orderByValue($order = Criteria::ASC) Order by the value column
 *
 * @method     ChildGeneralTranslationQuery groupById() Group by the id column
 * @method     ChildGeneralTranslationQuery groupByLang() Group by the lang column
 * @method     ChildGeneralTranslationQuery groupByKey() Group by the key column
 * @method     ChildGeneralTranslationQuery groupByValue() Group by the value column
 *
 * @method     ChildGeneralTranslationQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildGeneralTranslationQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildGeneralTranslationQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildGeneralTranslationQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildGeneralTranslationQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildGeneralTranslationQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildGeneralTranslation|null findOne(?ConnectionInterface $con = null) Return the first ChildGeneralTranslation matching the query
 * @method     ChildGeneralTranslation findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildGeneralTranslation matching the query, or a new ChildGeneralTranslation object populated from the query conditions when no match is found
 *
 * @method     ChildGeneralTranslation|null findOneById(int $id) Return the first ChildGeneralTranslation filtered by the id column
 * @method     ChildGeneralTranslation|null findOneByLang(string $lang) Return the first ChildGeneralTranslation filtered by the lang column
 * @method     ChildGeneralTranslation|null findOneByKey(string $key) Return the first ChildGeneralTranslation filtered by the key column
 * @method     ChildGeneralTranslation|null findOneByValue(string $value) Return the first ChildGeneralTranslation filtered by the value column
 *
 * @method     ChildGeneralTranslation requirePk($key, ?ConnectionInterface $con = null) Return the ChildGeneralTranslation by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGeneralTranslation requireOne(?ConnectionInterface $con = null) Return the first ChildGeneralTranslation matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildGeneralTranslation requireOneById(int $id) Return the first ChildGeneralTranslation filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGeneralTranslation requireOneByLang(string $lang) Return the first ChildGeneralTranslation filtered by the lang column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGeneralTranslation requireOneByKey(string $key) Return the first ChildGeneralTranslation filtered by the key column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGeneralTranslation requireOneByValue(string $value) Return the first ChildGeneralTranslation filtered by the value column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildGeneralTranslation[]|Collection find(?ConnectionInterface $con = null) Return ChildGeneralTranslation objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildGeneralTranslation> find(?ConnectionInterface $con = null) Return ChildGeneralTranslation objects based on current ModelCriteria
 *
 * @method     ChildGeneralTranslation[]|Collection findById(int|array<int> $id) Return ChildGeneralTranslation objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildGeneralTranslation> findById(int|array<int> $id) Return ChildGeneralTranslation objects filtered by the id column
 * @method     ChildGeneralTranslation[]|Collection findByLang(string|array<string> $lang) Return ChildGeneralTranslation objects filtered by the lang column
 * @psalm-method Collection&\Traversable<ChildGeneralTranslation> findByLang(string|array<string> $lang) Return ChildGeneralTranslation objects filtered by the lang column
 * @method     ChildGeneralTranslation[]|Collection findByKey(string|array<string> $key) Return ChildGeneralTranslation objects filtered by the key column
 * @psalm-method Collection&\Traversable<ChildGeneralTranslation> findByKey(string|array<string> $key) Return ChildGeneralTranslation objects filtered by the key column
 * @method     ChildGeneralTranslation[]|Collection findByValue(string|array<string> $value) Return ChildGeneralTranslation objects filtered by the value column
 * @psalm-method Collection&\Traversable<ChildGeneralTranslation> findByValue(string|array<string> $value) Return ChildGeneralTranslation objects filtered by the value column
 *
 * @method     ChildGeneralTranslation[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildGeneralTranslation> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class GeneralTranslationQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \PromCMS\Core\Models\Base\GeneralTranslationQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'core', $modelName = '\\PromCMS\\Core\\Models\\GeneralTranslation', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildGeneralTranslationQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildGeneralTranslationQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildGeneralTranslationQuery) {
            return $criteria;
        }
        $query = new ChildGeneralTranslationQuery();
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
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildGeneralTranslation|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(GeneralTranslationTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = GeneralTranslationTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildGeneralTranslation A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, lang, key, value FROM prom__general_translations WHERE id = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildGeneralTranslation $obj */
            $obj = new ChildGeneralTranslation();
            $obj->hydrate($row);
            GeneralTranslationTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildGeneralTranslation|array|mixed the result, formatted by the current formatter
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
     * $objs = $c->findPks(array(12, 56, 832), $con);
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

        $this->addUsingAlias(GeneralTranslationTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(GeneralTranslationTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(GeneralTranslationTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(GeneralTranslationTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(GeneralTranslationTableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the lang column
     *
     * Example usage:
     * <code>
     * $query->filterByLang('fooValue');   // WHERE lang = 'fooValue'
     * $query->filterByLang('%fooValue%', Criteria::LIKE); // WHERE lang LIKE '%fooValue%'
     * $query->filterByLang(['foo', 'bar']); // WHERE lang IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $lang The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByLang($lang = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($lang)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(GeneralTranslationTableMap::COL_LANG, $lang, $comparison);

        return $this;
    }

    /**
     * Filter the query on the key column
     *
     * Example usage:
     * <code>
     * $query->filterByKey('fooValue');   // WHERE key = 'fooValue'
     * $query->filterByKey('%fooValue%', Criteria::LIKE); // WHERE key LIKE '%fooValue%'
     * $query->filterByKey(['foo', 'bar']); // WHERE key IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $key The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByKey($key = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($key)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(GeneralTranslationTableMap::COL_KEY, $key, $comparison);

        return $this;
    }

    /**
     * Filter the query on the value column
     *
     * Example usage:
     * <code>
     * $query->filterByValue('fooValue');   // WHERE value = 'fooValue'
     * $query->filterByValue('%fooValue%', Criteria::LIKE); // WHERE value LIKE '%fooValue%'
     * $query->filterByValue(['foo', 'bar']); // WHERE value IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $value The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByValue($value = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($value)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(GeneralTranslationTableMap::COL_VALUE, $value, $comparison);

        return $this;
    }

    /**
     * Exclude object from result
     *
     * @param ChildGeneralTranslation $generalTranslation Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($generalTranslation = null)
    {
        if ($generalTranslation) {
            $this->addUsingAlias(GeneralTranslationTableMap::COL_ID, $generalTranslation->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the prom__general_translations table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(GeneralTranslationTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            GeneralTranslationTableMap::clearInstancePool();
            GeneralTranslationTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(GeneralTranslationTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(GeneralTranslationTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            GeneralTranslationTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            GeneralTranslationTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
