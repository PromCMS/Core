<?php

namespace PromCMS\Core\Models\Base;

use \Exception;
use \PDO;
use PromCMS\Core\Models\User as ChildUser;
use PromCMS\Core\Models\UserQuery as ChildUserQuery;
use PromCMS\Core\Models\Map\UserTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the `prom__users` table.
 *
 * @method     ChildUserQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildUserQuery orderByEmail($order = Criteria::ASC) Order by the email column
 * @method     ChildUserQuery orderByPassword($order = Criteria::ASC) Order by the password column
 * @method     ChildUserQuery orderByFirstname($order = Criteria::ASC) Order by the firstname column
 * @method     ChildUserQuery orderByLastname($order = Criteria::ASC) Order by the lastname column
 * @method     ChildUserQuery orderByState($order = Criteria::ASC) Order by the state column
 * @method     ChildUserQuery orderByAvatarId($order = Criteria::ASC) Order by the avatar_id column
 * @method     ChildUserQuery orderByRoleId($order = Criteria::ASC) Order by the role_id column
 *
 * @method     ChildUserQuery groupById() Group by the id column
 * @method     ChildUserQuery groupByEmail() Group by the email column
 * @method     ChildUserQuery groupByPassword() Group by the password column
 * @method     ChildUserQuery groupByFirstname() Group by the firstname column
 * @method     ChildUserQuery groupByLastname() Group by the lastname column
 * @method     ChildUserQuery groupByState() Group by the state column
 * @method     ChildUserQuery groupByAvatarId() Group by the avatar_id column
 * @method     ChildUserQuery groupByRoleId() Group by the role_id column
 *
 * @method     ChildUserQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildUserQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildUserQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildUserQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildUserQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildUserQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildUserQuery leftJoinFileRelatedByAvatarId($relationAlias = null) Adds a LEFT JOIN clause to the query using the FileRelatedByAvatarId relation
 * @method     ChildUserQuery rightJoinFileRelatedByAvatarId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the FileRelatedByAvatarId relation
 * @method     ChildUserQuery innerJoinFileRelatedByAvatarId($relationAlias = null) Adds a INNER JOIN clause to the query using the FileRelatedByAvatarId relation
 *
 * @method     ChildUserQuery joinWithFileRelatedByAvatarId($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the FileRelatedByAvatarId relation
 *
 * @method     ChildUserQuery leftJoinWithFileRelatedByAvatarId() Adds a LEFT JOIN clause and with to the query using the FileRelatedByAvatarId relation
 * @method     ChildUserQuery rightJoinWithFileRelatedByAvatarId() Adds a RIGHT JOIN clause and with to the query using the FileRelatedByAvatarId relation
 * @method     ChildUserQuery innerJoinWithFileRelatedByAvatarId() Adds a INNER JOIN clause and with to the query using the FileRelatedByAvatarId relation
 *
 * @method     ChildUserQuery leftJoinUserRole($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserRole relation
 * @method     ChildUserQuery rightJoinUserRole($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserRole relation
 * @method     ChildUserQuery innerJoinUserRole($relationAlias = null) Adds a INNER JOIN clause to the query using the UserRole relation
 *
 * @method     ChildUserQuery joinWithUserRole($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the UserRole relation
 *
 * @method     ChildUserQuery leftJoinWithUserRole() Adds a LEFT JOIN clause and with to the query using the UserRole relation
 * @method     ChildUserQuery rightJoinWithUserRole() Adds a RIGHT JOIN clause and with to the query using the UserRole relation
 * @method     ChildUserQuery innerJoinWithUserRole() Adds a INNER JOIN clause and with to the query using the UserRole relation
 *
 * @method     ChildUserQuery leftJoinFileRelatedByCreatedBy($relationAlias = null) Adds a LEFT JOIN clause to the query using the FileRelatedByCreatedBy relation
 * @method     ChildUserQuery rightJoinFileRelatedByCreatedBy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the FileRelatedByCreatedBy relation
 * @method     ChildUserQuery innerJoinFileRelatedByCreatedBy($relationAlias = null) Adds a INNER JOIN clause to the query using the FileRelatedByCreatedBy relation
 *
 * @method     ChildUserQuery joinWithFileRelatedByCreatedBy($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the FileRelatedByCreatedBy relation
 *
 * @method     ChildUserQuery leftJoinWithFileRelatedByCreatedBy() Adds a LEFT JOIN clause and with to the query using the FileRelatedByCreatedBy relation
 * @method     ChildUserQuery rightJoinWithFileRelatedByCreatedBy() Adds a RIGHT JOIN clause and with to the query using the FileRelatedByCreatedBy relation
 * @method     ChildUserQuery innerJoinWithFileRelatedByCreatedBy() Adds a INNER JOIN clause and with to the query using the FileRelatedByCreatedBy relation
 *
 * @method     ChildUserQuery leftJoinFileRelatedByUpdatedBy($relationAlias = null) Adds a LEFT JOIN clause to the query using the FileRelatedByUpdatedBy relation
 * @method     ChildUserQuery rightJoinFileRelatedByUpdatedBy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the FileRelatedByUpdatedBy relation
 * @method     ChildUserQuery innerJoinFileRelatedByUpdatedBy($relationAlias = null) Adds a INNER JOIN clause to the query using the FileRelatedByUpdatedBy relation
 *
 * @method     ChildUserQuery joinWithFileRelatedByUpdatedBy($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the FileRelatedByUpdatedBy relation
 *
 * @method     ChildUserQuery leftJoinWithFileRelatedByUpdatedBy() Adds a LEFT JOIN clause and with to the query using the FileRelatedByUpdatedBy relation
 * @method     ChildUserQuery rightJoinWithFileRelatedByUpdatedBy() Adds a RIGHT JOIN clause and with to the query using the FileRelatedByUpdatedBy relation
 * @method     ChildUserQuery innerJoinWithFileRelatedByUpdatedBy() Adds a INNER JOIN clause and with to the query using the FileRelatedByUpdatedBy relation
 *
 * @method     ChildUserQuery leftJoinSettingRelatedByCreatedBy($relationAlias = null) Adds a LEFT JOIN clause to the query using the SettingRelatedByCreatedBy relation
 * @method     ChildUserQuery rightJoinSettingRelatedByCreatedBy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the SettingRelatedByCreatedBy relation
 * @method     ChildUserQuery innerJoinSettingRelatedByCreatedBy($relationAlias = null) Adds a INNER JOIN clause to the query using the SettingRelatedByCreatedBy relation
 *
 * @method     ChildUserQuery joinWithSettingRelatedByCreatedBy($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the SettingRelatedByCreatedBy relation
 *
 * @method     ChildUserQuery leftJoinWithSettingRelatedByCreatedBy() Adds a LEFT JOIN clause and with to the query using the SettingRelatedByCreatedBy relation
 * @method     ChildUserQuery rightJoinWithSettingRelatedByCreatedBy() Adds a RIGHT JOIN clause and with to the query using the SettingRelatedByCreatedBy relation
 * @method     ChildUserQuery innerJoinWithSettingRelatedByCreatedBy() Adds a INNER JOIN clause and with to the query using the SettingRelatedByCreatedBy relation
 *
 * @method     ChildUserQuery leftJoinSettingRelatedByUpdatedBy($relationAlias = null) Adds a LEFT JOIN clause to the query using the SettingRelatedByUpdatedBy relation
 * @method     ChildUserQuery rightJoinSettingRelatedByUpdatedBy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the SettingRelatedByUpdatedBy relation
 * @method     ChildUserQuery innerJoinSettingRelatedByUpdatedBy($relationAlias = null) Adds a INNER JOIN clause to the query using the SettingRelatedByUpdatedBy relation
 *
 * @method     ChildUserQuery joinWithSettingRelatedByUpdatedBy($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the SettingRelatedByUpdatedBy relation
 *
 * @method     ChildUserQuery leftJoinWithSettingRelatedByUpdatedBy() Adds a LEFT JOIN clause and with to the query using the SettingRelatedByUpdatedBy relation
 * @method     ChildUserQuery rightJoinWithSettingRelatedByUpdatedBy() Adds a RIGHT JOIN clause and with to the query using the SettingRelatedByUpdatedBy relation
 * @method     ChildUserQuery innerJoinWithSettingRelatedByUpdatedBy() Adds a INNER JOIN clause and with to the query using the SettingRelatedByUpdatedBy relation
 *
 * @method     \PromCMS\Core\Models\FileQuery|\PromCMS\Core\Models\UserRoleQuery|\PromCMS\Core\Models\FileQuery|\PromCMS\Core\Models\FileQuery|\PromCMS\Core\Models\SettingQuery|\PromCMS\Core\Models\SettingQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildUser|null findOne(?ConnectionInterface $con = null) Return the first ChildUser matching the query
 * @method     ChildUser findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildUser matching the query, or a new ChildUser object populated from the query conditions when no match is found
 *
 * @method     ChildUser|null findOneById(int $id) Return the first ChildUser filtered by the id column
 * @method     ChildUser|null findOneByEmail(string $email) Return the first ChildUser filtered by the email column
 * @method     ChildUser|null findOneByPassword(string $password) Return the first ChildUser filtered by the password column
 * @method     ChildUser|null findOneByFirstname(string $firstname) Return the first ChildUser filtered by the firstname column
 * @method     ChildUser|null findOneByLastname(string $lastname) Return the first ChildUser filtered by the lastname column
 * @method     ChildUser|null findOneByState(int $state) Return the first ChildUser filtered by the state column
 * @method     ChildUser|null findOneByAvatarId(int $avatar_id) Return the first ChildUser filtered by the avatar_id column
 * @method     ChildUser|null findOneByRoleId(int $role_id) Return the first ChildUser filtered by the role_id column
 *
 * @method     ChildUser requirePk($key, ?ConnectionInterface $con = null) Return the ChildUser by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOne(?ConnectionInterface $con = null) Return the first ChildUser matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUser requireOneById(int $id) Return the first ChildUser filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByEmail(string $email) Return the first ChildUser filtered by the email column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByPassword(string $password) Return the first ChildUser filtered by the password column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByFirstname(string $firstname) Return the first ChildUser filtered by the firstname column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByLastname(string $lastname) Return the first ChildUser filtered by the lastname column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByState(int $state) Return the first ChildUser filtered by the state column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByAvatarId(int $avatar_id) Return the first ChildUser filtered by the avatar_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByRoleId(int $role_id) Return the first ChildUser filtered by the role_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUser[]|Collection find(?ConnectionInterface $con = null) Return ChildUser objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildUser> find(?ConnectionInterface $con = null) Return ChildUser objects based on current ModelCriteria
 *
 * @method     ChildUser[]|Collection findById(int|array<int> $id) Return ChildUser objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildUser> findById(int|array<int> $id) Return ChildUser objects filtered by the id column
 * @method     ChildUser[]|Collection findByEmail(string|array<string> $email) Return ChildUser objects filtered by the email column
 * @psalm-method Collection&\Traversable<ChildUser> findByEmail(string|array<string> $email) Return ChildUser objects filtered by the email column
 * @method     ChildUser[]|Collection findByPassword(string|array<string> $password) Return ChildUser objects filtered by the password column
 * @psalm-method Collection&\Traversable<ChildUser> findByPassword(string|array<string> $password) Return ChildUser objects filtered by the password column
 * @method     ChildUser[]|Collection findByFirstname(string|array<string> $firstname) Return ChildUser objects filtered by the firstname column
 * @psalm-method Collection&\Traversable<ChildUser> findByFirstname(string|array<string> $firstname) Return ChildUser objects filtered by the firstname column
 * @method     ChildUser[]|Collection findByLastname(string|array<string> $lastname) Return ChildUser objects filtered by the lastname column
 * @psalm-method Collection&\Traversable<ChildUser> findByLastname(string|array<string> $lastname) Return ChildUser objects filtered by the lastname column
 * @method     ChildUser[]|Collection findByState(int|array<int> $state) Return ChildUser objects filtered by the state column
 * @psalm-method Collection&\Traversable<ChildUser> findByState(int|array<int> $state) Return ChildUser objects filtered by the state column
 * @method     ChildUser[]|Collection findByAvatarId(int|array<int> $avatar_id) Return ChildUser objects filtered by the avatar_id column
 * @psalm-method Collection&\Traversable<ChildUser> findByAvatarId(int|array<int> $avatar_id) Return ChildUser objects filtered by the avatar_id column
 * @method     ChildUser[]|Collection findByRoleId(int|array<int> $role_id) Return ChildUser objects filtered by the role_id column
 * @psalm-method Collection&\Traversable<ChildUser> findByRoleId(int|array<int> $role_id) Return ChildUser objects filtered by the role_id column
 *
 * @method     ChildUser[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildUser> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class UserQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \PromCMS\Core\Models\Base\UserQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'core', $modelName = '\\PromCMS\\Core\\Models\\User', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildUserQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildUserQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildUserQuery) {
            return $criteria;
        }
        $query = new ChildUserQuery();
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
     * @return ChildUser|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(UserTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = UserTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildUser A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, email, password, firstname, lastname, state, avatar_id, role_id FROM prom__users WHERE id = :p0';
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
            /** @var ChildUser $obj */
            $obj = new ChildUser();
            $obj->hydrate($row);
            UserTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildUser|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(UserTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(UserTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(UserTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(UserTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(UserTableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the email column
     *
     * Example usage:
     * <code>
     * $query->filterByEmail('fooValue');   // WHERE email = 'fooValue'
     * $query->filterByEmail('%fooValue%', Criteria::LIKE); // WHERE email LIKE '%fooValue%'
     * $query->filterByEmail(['foo', 'bar']); // WHERE email IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $email The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByEmail($email = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($email)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(UserTableMap::COL_EMAIL, $email, $comparison);

        return $this;
    }

    /**
     * Filter the query on the password column
     *
     * Example usage:
     * <code>
     * $query->filterByPassword('fooValue');   // WHERE password = 'fooValue'
     * $query->filterByPassword('%fooValue%', Criteria::LIKE); // WHERE password LIKE '%fooValue%'
     * $query->filterByPassword(['foo', 'bar']); // WHERE password IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $password The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPassword($password = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($password)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(UserTableMap::COL_PASSWORD, $password, $comparison);

        return $this;
    }

    /**
     * Filter the query on the firstname column
     *
     * Example usage:
     * <code>
     * $query->filterByFirstname('fooValue');   // WHERE firstname = 'fooValue'
     * $query->filterByFirstname('%fooValue%', Criteria::LIKE); // WHERE firstname LIKE '%fooValue%'
     * $query->filterByFirstname(['foo', 'bar']); // WHERE firstname IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $firstname The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByFirstname($firstname = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($firstname)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(UserTableMap::COL_FIRSTNAME, $firstname, $comparison);

        return $this;
    }

    /**
     * Filter the query on the lastname column
     *
     * Example usage:
     * <code>
     * $query->filterByLastname('fooValue');   // WHERE lastname = 'fooValue'
     * $query->filterByLastname('%fooValue%', Criteria::LIKE); // WHERE lastname LIKE '%fooValue%'
     * $query->filterByLastname(['foo', 'bar']); // WHERE lastname IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $lastname The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByLastname($lastname = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($lastname)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(UserTableMap::COL_LASTNAME, $lastname, $comparison);

        return $this;
    }

    /**
     * Filter the query on the state column
     *
     * @param mixed $state The value to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByState($state = null, ?string $comparison = null)
    {
        $valueSet = UserTableMap::getValueSet(UserTableMap::COL_STATE);
        if (is_scalar($state)) {
            if (!in_array($state, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $state));
            }
            $state = array_search($state, $valueSet);
        } elseif (is_array($state)) {
            $convertedValues = [];
            foreach ($state as $value) {
                if (!in_array($value, $valueSet)) {
                    throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $value));
                }
                $convertedValues []= array_search($value, $valueSet);
            }
            $state = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(UserTableMap::COL_STATE, $state, $comparison);

        return $this;
    }

    /**
     * Filter the query on the avatar_id column
     *
     * Example usage:
     * <code>
     * $query->filterByAvatarId(1234); // WHERE avatar_id = 1234
     * $query->filterByAvatarId(array(12, 34)); // WHERE avatar_id IN (12, 34)
     * $query->filterByAvatarId(array('min' => 12)); // WHERE avatar_id > 12
     * </code>
     *
     * @see       filterByFileRelatedByAvatarId()
     *
     * @param mixed $avatarId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByAvatarId($avatarId = null, ?string $comparison = null)
    {
        if (is_array($avatarId)) {
            $useMinMax = false;
            if (isset($avatarId['min'])) {
                $this->addUsingAlias(UserTableMap::COL_AVATAR_ID, $avatarId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($avatarId['max'])) {
                $this->addUsingAlias(UserTableMap::COL_AVATAR_ID, $avatarId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(UserTableMap::COL_AVATAR_ID, $avatarId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the role_id column
     *
     * Example usage:
     * <code>
     * $query->filterByRoleId(1234); // WHERE role_id = 1234
     * $query->filterByRoleId(array(12, 34)); // WHERE role_id IN (12, 34)
     * $query->filterByRoleId(array('min' => 12)); // WHERE role_id > 12
     * </code>
     *
     * @see       filterByUserRole()
     *
     * @param mixed $roleId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByRoleId($roleId = null, ?string $comparison = null)
    {
        if (is_array($roleId)) {
            $useMinMax = false;
            if (isset($roleId['min'])) {
                $this->addUsingAlias(UserTableMap::COL_ROLE_ID, $roleId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($roleId['max'])) {
                $this->addUsingAlias(UserTableMap::COL_ROLE_ID, $roleId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(UserTableMap::COL_ROLE_ID, $roleId, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \PromCMS\Core\Models\File object
     *
     * @param \PromCMS\Core\Models\File|ObjectCollection $file The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByFileRelatedByAvatarId($file, ?string $comparison = null)
    {
        if ($file instanceof \PromCMS\Core\Models\File) {
            return $this
                ->addUsingAlias(UserTableMap::COL_AVATAR_ID, $file->getId(), $comparison);
        } elseif ($file instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(UserTableMap::COL_AVATAR_ID, $file->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByFileRelatedByAvatarId() only accepts arguments of type \PromCMS\Core\Models\File or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the FileRelatedByAvatarId relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinFileRelatedByAvatarId(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('FileRelatedByAvatarId');

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
            $this->addJoinObject($join, 'FileRelatedByAvatarId');
        }

        return $this;
    }

    /**
     * Use the FileRelatedByAvatarId relation File object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \PromCMS\Core\Models\FileQuery A secondary query class using the current class as primary query
     */
    public function useFileRelatedByAvatarIdQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinFileRelatedByAvatarId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'FileRelatedByAvatarId', '\PromCMS\Core\Models\FileQuery');
    }

    /**
     * Use the FileRelatedByAvatarId relation File object
     *
     * @param callable(\PromCMS\Core\Models\FileQuery):\PromCMS\Core\Models\FileQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withFileRelatedByAvatarIdQuery(
        callable $callable,
        string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useFileRelatedByAvatarIdQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the FileRelatedByAvatarId relation to the File table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \PromCMS\Core\Models\FileQuery The inner query object of the EXISTS statement
     */
    public function useFileRelatedByAvatarIdExistsQuery($modelAlias = null, $queryClass = null, $typeOfExists = 'EXISTS')
    {
        /** @var $q \PromCMS\Core\Models\FileQuery */
        $q = $this->useExistsQuery('FileRelatedByAvatarId', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the FileRelatedByAvatarId relation to the File table for a NOT EXISTS query.
     *
     * @see useFileRelatedByAvatarIdExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \PromCMS\Core\Models\FileQuery The inner query object of the NOT EXISTS statement
     */
    public function useFileRelatedByAvatarIdNotExistsQuery($modelAlias = null, $queryClass = null)
    {
        /** @var $q \PromCMS\Core\Models\FileQuery */
        $q = $this->useExistsQuery('FileRelatedByAvatarId', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the FileRelatedByAvatarId relation to the File table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \PromCMS\Core\Models\FileQuery The inner query object of the IN statement
     */
    public function useInFileRelatedByAvatarIdQuery($modelAlias = null, $queryClass = null, $typeOfIn = 'IN')
    {
        /** @var $q \PromCMS\Core\Models\FileQuery */
        $q = $this->useInQuery('FileRelatedByAvatarId', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the FileRelatedByAvatarId relation to the File table for a NOT IN query.
     *
     * @see useFileRelatedByAvatarIdInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \PromCMS\Core\Models\FileQuery The inner query object of the NOT IN statement
     */
    public function useNotInFileRelatedByAvatarIdQuery($modelAlias = null, $queryClass = null)
    {
        /** @var $q \PromCMS\Core\Models\FileQuery */
        $q = $this->useInQuery('FileRelatedByAvatarId', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \PromCMS\Core\Models\UserRole object
     *
     * @param \PromCMS\Core\Models\UserRole|ObjectCollection $userRole The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByUserRole($userRole, ?string $comparison = null)
    {
        if ($userRole instanceof \PromCMS\Core\Models\UserRole) {
            return $this
                ->addUsingAlias(UserTableMap::COL_ROLE_ID, $userRole->getId(), $comparison);
        } elseif ($userRole instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(UserTableMap::COL_ROLE_ID, $userRole->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByUserRole() only accepts arguments of type \PromCMS\Core\Models\UserRole or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the UserRole relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinUserRole(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('UserRole');

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
            $this->addJoinObject($join, 'UserRole');
        }

        return $this;
    }

    /**
     * Use the UserRole relation UserRole object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \PromCMS\Core\Models\UserRoleQuery A secondary query class using the current class as primary query
     */
    public function useUserRoleQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinUserRole($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'UserRole', '\PromCMS\Core\Models\UserRoleQuery');
    }

    /**
     * Use the UserRole relation UserRole object
     *
     * @param callable(\PromCMS\Core\Models\UserRoleQuery):\PromCMS\Core\Models\UserRoleQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withUserRoleQuery(
        callable $callable,
        string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useUserRoleQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to UserRole table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \PromCMS\Core\Models\UserRoleQuery The inner query object of the EXISTS statement
     */
    public function useUserRoleExistsQuery($modelAlias = null, $queryClass = null, $typeOfExists = 'EXISTS')
    {
        /** @var $q \PromCMS\Core\Models\UserRoleQuery */
        $q = $this->useExistsQuery('UserRole', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to UserRole table for a NOT EXISTS query.
     *
     * @see useUserRoleExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \PromCMS\Core\Models\UserRoleQuery The inner query object of the NOT EXISTS statement
     */
    public function useUserRoleNotExistsQuery($modelAlias = null, $queryClass = null)
    {
        /** @var $q \PromCMS\Core\Models\UserRoleQuery */
        $q = $this->useExistsQuery('UserRole', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to UserRole table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \PromCMS\Core\Models\UserRoleQuery The inner query object of the IN statement
     */
    public function useInUserRoleQuery($modelAlias = null, $queryClass = null, $typeOfIn = 'IN')
    {
        /** @var $q \PromCMS\Core\Models\UserRoleQuery */
        $q = $this->useInQuery('UserRole', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to UserRole table for a NOT IN query.
     *
     * @see useUserRoleInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \PromCMS\Core\Models\UserRoleQuery The inner query object of the NOT IN statement
     */
    public function useNotInUserRoleQuery($modelAlias = null, $queryClass = null)
    {
        /** @var $q \PromCMS\Core\Models\UserRoleQuery */
        $q = $this->useInQuery('UserRole', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \PromCMS\Core\Models\File object
     *
     * @param \PromCMS\Core\Models\File|ObjectCollection $file the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByFileRelatedByCreatedBy($file, ?string $comparison = null)
    {
        if ($file instanceof \PromCMS\Core\Models\File) {
            $this
                ->addUsingAlias(UserTableMap::COL_ID, $file->getCreatedBy(), $comparison);

            return $this;
        } elseif ($file instanceof ObjectCollection) {
            $this
                ->useFileRelatedByCreatedByQuery()
                ->filterByPrimaryKeys($file->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByFileRelatedByCreatedBy() only accepts arguments of type \PromCMS\Core\Models\File or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the FileRelatedByCreatedBy relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinFileRelatedByCreatedBy(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('FileRelatedByCreatedBy');

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
            $this->addJoinObject($join, 'FileRelatedByCreatedBy');
        }

        return $this;
    }

    /**
     * Use the FileRelatedByCreatedBy relation File object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \PromCMS\Core\Models\FileQuery A secondary query class using the current class as primary query
     */
    public function useFileRelatedByCreatedByQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinFileRelatedByCreatedBy($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'FileRelatedByCreatedBy', '\PromCMS\Core\Models\FileQuery');
    }

    /**
     * Use the FileRelatedByCreatedBy relation File object
     *
     * @param callable(\PromCMS\Core\Models\FileQuery):\PromCMS\Core\Models\FileQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withFileRelatedByCreatedByQuery(
        callable $callable,
        string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useFileRelatedByCreatedByQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the FileRelatedByCreatedBy relation to the File table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \PromCMS\Core\Models\FileQuery The inner query object of the EXISTS statement
     */
    public function useFileRelatedByCreatedByExistsQuery($modelAlias = null, $queryClass = null, $typeOfExists = 'EXISTS')
    {
        /** @var $q \PromCMS\Core\Models\FileQuery */
        $q = $this->useExistsQuery('FileRelatedByCreatedBy', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the FileRelatedByCreatedBy relation to the File table for a NOT EXISTS query.
     *
     * @see useFileRelatedByCreatedByExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \PromCMS\Core\Models\FileQuery The inner query object of the NOT EXISTS statement
     */
    public function useFileRelatedByCreatedByNotExistsQuery($modelAlias = null, $queryClass = null)
    {
        /** @var $q \PromCMS\Core\Models\FileQuery */
        $q = $this->useExistsQuery('FileRelatedByCreatedBy', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the FileRelatedByCreatedBy relation to the File table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \PromCMS\Core\Models\FileQuery The inner query object of the IN statement
     */
    public function useInFileRelatedByCreatedByQuery($modelAlias = null, $queryClass = null, $typeOfIn = 'IN')
    {
        /** @var $q \PromCMS\Core\Models\FileQuery */
        $q = $this->useInQuery('FileRelatedByCreatedBy', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the FileRelatedByCreatedBy relation to the File table for a NOT IN query.
     *
     * @see useFileRelatedByCreatedByInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \PromCMS\Core\Models\FileQuery The inner query object of the NOT IN statement
     */
    public function useNotInFileRelatedByCreatedByQuery($modelAlias = null, $queryClass = null)
    {
        /** @var $q \PromCMS\Core\Models\FileQuery */
        $q = $this->useInQuery('FileRelatedByCreatedBy', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \PromCMS\Core\Models\File object
     *
     * @param \PromCMS\Core\Models\File|ObjectCollection $file the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByFileRelatedByUpdatedBy($file, ?string $comparison = null)
    {
        if ($file instanceof \PromCMS\Core\Models\File) {
            $this
                ->addUsingAlias(UserTableMap::COL_ID, $file->getUpdatedBy(), $comparison);

            return $this;
        } elseif ($file instanceof ObjectCollection) {
            $this
                ->useFileRelatedByUpdatedByQuery()
                ->filterByPrimaryKeys($file->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByFileRelatedByUpdatedBy() only accepts arguments of type \PromCMS\Core\Models\File or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the FileRelatedByUpdatedBy relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinFileRelatedByUpdatedBy(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('FileRelatedByUpdatedBy');

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
            $this->addJoinObject($join, 'FileRelatedByUpdatedBy');
        }

        return $this;
    }

    /**
     * Use the FileRelatedByUpdatedBy relation File object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \PromCMS\Core\Models\FileQuery A secondary query class using the current class as primary query
     */
    public function useFileRelatedByUpdatedByQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinFileRelatedByUpdatedBy($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'FileRelatedByUpdatedBy', '\PromCMS\Core\Models\FileQuery');
    }

    /**
     * Use the FileRelatedByUpdatedBy relation File object
     *
     * @param callable(\PromCMS\Core\Models\FileQuery):\PromCMS\Core\Models\FileQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withFileRelatedByUpdatedByQuery(
        callable $callable,
        string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useFileRelatedByUpdatedByQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the FileRelatedByUpdatedBy relation to the File table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \PromCMS\Core\Models\FileQuery The inner query object of the EXISTS statement
     */
    public function useFileRelatedByUpdatedByExistsQuery($modelAlias = null, $queryClass = null, $typeOfExists = 'EXISTS')
    {
        /** @var $q \PromCMS\Core\Models\FileQuery */
        $q = $this->useExistsQuery('FileRelatedByUpdatedBy', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the FileRelatedByUpdatedBy relation to the File table for a NOT EXISTS query.
     *
     * @see useFileRelatedByUpdatedByExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \PromCMS\Core\Models\FileQuery The inner query object of the NOT EXISTS statement
     */
    public function useFileRelatedByUpdatedByNotExistsQuery($modelAlias = null, $queryClass = null)
    {
        /** @var $q \PromCMS\Core\Models\FileQuery */
        $q = $this->useExistsQuery('FileRelatedByUpdatedBy', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the FileRelatedByUpdatedBy relation to the File table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \PromCMS\Core\Models\FileQuery The inner query object of the IN statement
     */
    public function useInFileRelatedByUpdatedByQuery($modelAlias = null, $queryClass = null, $typeOfIn = 'IN')
    {
        /** @var $q \PromCMS\Core\Models\FileQuery */
        $q = $this->useInQuery('FileRelatedByUpdatedBy', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the FileRelatedByUpdatedBy relation to the File table for a NOT IN query.
     *
     * @see useFileRelatedByUpdatedByInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \PromCMS\Core\Models\FileQuery The inner query object of the NOT IN statement
     */
    public function useNotInFileRelatedByUpdatedByQuery($modelAlias = null, $queryClass = null)
    {
        /** @var $q \PromCMS\Core\Models\FileQuery */
        $q = $this->useInQuery('FileRelatedByUpdatedBy', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \PromCMS\Core\Models\Setting object
     *
     * @param \PromCMS\Core\Models\Setting|ObjectCollection $setting the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterBySettingRelatedByCreatedBy($setting, ?string $comparison = null)
    {
        if ($setting instanceof \PromCMS\Core\Models\Setting) {
            $this
                ->addUsingAlias(UserTableMap::COL_ID, $setting->getCreatedBy(), $comparison);

            return $this;
        } elseif ($setting instanceof ObjectCollection) {
            $this
                ->useSettingRelatedByCreatedByQuery()
                ->filterByPrimaryKeys($setting->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterBySettingRelatedByCreatedBy() only accepts arguments of type \PromCMS\Core\Models\Setting or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the SettingRelatedByCreatedBy relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinSettingRelatedByCreatedBy(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('SettingRelatedByCreatedBy');

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
            $this->addJoinObject($join, 'SettingRelatedByCreatedBy');
        }

        return $this;
    }

    /**
     * Use the SettingRelatedByCreatedBy relation Setting object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \PromCMS\Core\Models\SettingQuery A secondary query class using the current class as primary query
     */
    public function useSettingRelatedByCreatedByQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinSettingRelatedByCreatedBy($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'SettingRelatedByCreatedBy', '\PromCMS\Core\Models\SettingQuery');
    }

    /**
     * Use the SettingRelatedByCreatedBy relation Setting object
     *
     * @param callable(\PromCMS\Core\Models\SettingQuery):\PromCMS\Core\Models\SettingQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withSettingRelatedByCreatedByQuery(
        callable $callable,
        string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useSettingRelatedByCreatedByQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the SettingRelatedByCreatedBy relation to the Setting table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \PromCMS\Core\Models\SettingQuery The inner query object of the EXISTS statement
     */
    public function useSettingRelatedByCreatedByExistsQuery($modelAlias = null, $queryClass = null, $typeOfExists = 'EXISTS')
    {
        /** @var $q \PromCMS\Core\Models\SettingQuery */
        $q = $this->useExistsQuery('SettingRelatedByCreatedBy', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the SettingRelatedByCreatedBy relation to the Setting table for a NOT EXISTS query.
     *
     * @see useSettingRelatedByCreatedByExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \PromCMS\Core\Models\SettingQuery The inner query object of the NOT EXISTS statement
     */
    public function useSettingRelatedByCreatedByNotExistsQuery($modelAlias = null, $queryClass = null)
    {
        /** @var $q \PromCMS\Core\Models\SettingQuery */
        $q = $this->useExistsQuery('SettingRelatedByCreatedBy', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the SettingRelatedByCreatedBy relation to the Setting table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \PromCMS\Core\Models\SettingQuery The inner query object of the IN statement
     */
    public function useInSettingRelatedByCreatedByQuery($modelAlias = null, $queryClass = null, $typeOfIn = 'IN')
    {
        /** @var $q \PromCMS\Core\Models\SettingQuery */
        $q = $this->useInQuery('SettingRelatedByCreatedBy', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the SettingRelatedByCreatedBy relation to the Setting table for a NOT IN query.
     *
     * @see useSettingRelatedByCreatedByInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \PromCMS\Core\Models\SettingQuery The inner query object of the NOT IN statement
     */
    public function useNotInSettingRelatedByCreatedByQuery($modelAlias = null, $queryClass = null)
    {
        /** @var $q \PromCMS\Core\Models\SettingQuery */
        $q = $this->useInQuery('SettingRelatedByCreatedBy', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \PromCMS\Core\Models\Setting object
     *
     * @param \PromCMS\Core\Models\Setting|ObjectCollection $setting the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterBySettingRelatedByUpdatedBy($setting, ?string $comparison = null)
    {
        if ($setting instanceof \PromCMS\Core\Models\Setting) {
            $this
                ->addUsingAlias(UserTableMap::COL_ID, $setting->getUpdatedBy(), $comparison);

            return $this;
        } elseif ($setting instanceof ObjectCollection) {
            $this
                ->useSettingRelatedByUpdatedByQuery()
                ->filterByPrimaryKeys($setting->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterBySettingRelatedByUpdatedBy() only accepts arguments of type \PromCMS\Core\Models\Setting or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the SettingRelatedByUpdatedBy relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinSettingRelatedByUpdatedBy(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('SettingRelatedByUpdatedBy');

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
            $this->addJoinObject($join, 'SettingRelatedByUpdatedBy');
        }

        return $this;
    }

    /**
     * Use the SettingRelatedByUpdatedBy relation Setting object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \PromCMS\Core\Models\SettingQuery A secondary query class using the current class as primary query
     */
    public function useSettingRelatedByUpdatedByQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinSettingRelatedByUpdatedBy($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'SettingRelatedByUpdatedBy', '\PromCMS\Core\Models\SettingQuery');
    }

    /**
     * Use the SettingRelatedByUpdatedBy relation Setting object
     *
     * @param callable(\PromCMS\Core\Models\SettingQuery):\PromCMS\Core\Models\SettingQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withSettingRelatedByUpdatedByQuery(
        callable $callable,
        string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useSettingRelatedByUpdatedByQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the SettingRelatedByUpdatedBy relation to the Setting table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \PromCMS\Core\Models\SettingQuery The inner query object of the EXISTS statement
     */
    public function useSettingRelatedByUpdatedByExistsQuery($modelAlias = null, $queryClass = null, $typeOfExists = 'EXISTS')
    {
        /** @var $q \PromCMS\Core\Models\SettingQuery */
        $q = $this->useExistsQuery('SettingRelatedByUpdatedBy', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the SettingRelatedByUpdatedBy relation to the Setting table for a NOT EXISTS query.
     *
     * @see useSettingRelatedByUpdatedByExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \PromCMS\Core\Models\SettingQuery The inner query object of the NOT EXISTS statement
     */
    public function useSettingRelatedByUpdatedByNotExistsQuery($modelAlias = null, $queryClass = null)
    {
        /** @var $q \PromCMS\Core\Models\SettingQuery */
        $q = $this->useExistsQuery('SettingRelatedByUpdatedBy', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the SettingRelatedByUpdatedBy relation to the Setting table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \PromCMS\Core\Models\SettingQuery The inner query object of the IN statement
     */
    public function useInSettingRelatedByUpdatedByQuery($modelAlias = null, $queryClass = null, $typeOfIn = 'IN')
    {
        /** @var $q \PromCMS\Core\Models\SettingQuery */
        $q = $this->useInQuery('SettingRelatedByUpdatedBy', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the SettingRelatedByUpdatedBy relation to the Setting table for a NOT IN query.
     *
     * @see useSettingRelatedByUpdatedByInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \PromCMS\Core\Models\SettingQuery The inner query object of the NOT IN statement
     */
    public function useNotInSettingRelatedByUpdatedByQuery($modelAlias = null, $queryClass = null)
    {
        /** @var $q \PromCMS\Core\Models\SettingQuery */
        $q = $this->useInQuery('SettingRelatedByUpdatedBy', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildUser $user Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($user = null)
    {
        if ($user) {
            $this->addUsingAlias(UserTableMap::COL_ID, $user->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the prom__users table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            UserTableMap::clearInstancePool();
            UserTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(UserTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(UserTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            UserTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            UserTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
