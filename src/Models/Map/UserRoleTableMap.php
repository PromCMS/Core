<?php

namespace PromCMS\Core\Models\Map;

use PromCMS\Core\Models\UserRole;
use PromCMS\Core\Models\UserRoleQuery;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;


/**
 * This class defines the structure of the 'prom__user_roles' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class UserRoleTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = '.Map.UserRoleTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'core';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'prom__user_roles';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'UserRole';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\PromCMS\\Core\\Models\\UserRole';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'UserRole';

    /**
     * The total number of columns
     */
    public const NUM_COLUMNS = 5;

    /**
     * The number of lazy-loaded columns
     */
    public const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    public const NUM_HYDRATE_COLUMNS = 5;

    /**
     * the column name for the id field
     */
    public const COL_ID = 'prom__user_roles.id';

    /**
     * the column name for the label field
     */
    public const COL_LABEL = 'prom__user_roles.label';

    /**
     * the column name for the description field
     */
    public const COL_DESCRIPTION = 'prom__user_roles.description';

    /**
     * the column name for the permissions field
     */
    public const COL_PERMISSIONS = 'prom__user_roles.permissions';

    /**
     * the column name for the slug field
     */
    public const COL_SLUG = 'prom__user_roles.slug';

    /**
     * The default string format for model objects of the related table
     */
    public const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     *
     * @var array<string, mixed>
     */
    protected static $fieldNames = [
        self::TYPE_PHPNAME       => ['Id', 'Label', 'Description', 'Permissions', 'Slug', ],
        self::TYPE_CAMELNAME     => ['id', 'label', 'description', 'permissions', 'slug', ],
        self::TYPE_COLNAME       => [UserRoleTableMap::COL_ID, UserRoleTableMap::COL_LABEL, UserRoleTableMap::COL_DESCRIPTION, UserRoleTableMap::COL_PERMISSIONS, UserRoleTableMap::COL_SLUG, ],
        self::TYPE_FIELDNAME     => ['id', 'label', 'description', 'permissions', 'slug', ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, ]
    ];

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     *
     * @var array<string, mixed>
     */
    protected static $fieldKeys = [
        self::TYPE_PHPNAME       => ['Id' => 0, 'Label' => 1, 'Description' => 2, 'Permissions' => 3, 'Slug' => 4, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'label' => 1, 'description' => 2, 'permissions' => 3, 'slug' => 4, ],
        self::TYPE_COLNAME       => [UserRoleTableMap::COL_ID => 0, UserRoleTableMap::COL_LABEL => 1, UserRoleTableMap::COL_DESCRIPTION => 2, UserRoleTableMap::COL_PERMISSIONS => 3, UserRoleTableMap::COL_SLUG => 4, ],
        self::TYPE_FIELDNAME     => ['id' => 0, 'label' => 1, 'description' => 2, 'permissions' => 3, 'slug' => 4, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected $normalizedColumnNameMap = [
        'Id' => 'ID',
        'UserRole.Id' => 'ID',
        'id' => 'ID',
        'userRole.id' => 'ID',
        'UserRoleTableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'prom__user_roles.id' => 'ID',
        'Label' => 'LABEL',
        'UserRole.Label' => 'LABEL',
        'label' => 'LABEL',
        'userRole.label' => 'LABEL',
        'UserRoleTableMap::COL_LABEL' => 'LABEL',
        'COL_LABEL' => 'LABEL',
        'prom__user_roles.label' => 'LABEL',
        'Description' => 'DESCRIPTION',
        'UserRole.Description' => 'DESCRIPTION',
        'description' => 'DESCRIPTION',
        'userRole.description' => 'DESCRIPTION',
        'UserRoleTableMap::COL_DESCRIPTION' => 'DESCRIPTION',
        'COL_DESCRIPTION' => 'DESCRIPTION',
        'prom__user_roles.description' => 'DESCRIPTION',
        'Permissions' => 'PERMISSIONS',
        'UserRole.Permissions' => 'PERMISSIONS',
        'permissions' => 'PERMISSIONS',
        'userRole.permissions' => 'PERMISSIONS',
        'UserRoleTableMap::COL_PERMISSIONS' => 'PERMISSIONS',
        'COL_PERMISSIONS' => 'PERMISSIONS',
        'prom__user_roles.permissions' => 'PERMISSIONS',
        'Slug' => 'SLUG',
        'UserRole.Slug' => 'SLUG',
        'slug' => 'SLUG',
        'userRole.slug' => 'SLUG',
        'UserRoleTableMap::COL_SLUG' => 'SLUG',
        'COL_SLUG' => 'SLUG',
        'prom__user_roles.slug' => 'SLUG',
    ];

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function initialize(): void
    {
        // attributes
        $this->setName('prom__user_roles');
        $this->setPhpName('UserRole');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\PromCMS\\Core\\Models\\UserRole');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('label', 'Label', 'VARCHAR', true, 255, null);
        $this->getColumn('label')->setPrimaryString(true);
        $this->addColumn('description', 'Description', 'LONGVARCHAR', false, null, null);
        $this->addColumn('permissions', 'Permissions', 'ARRAY', false, null, null);
        $this->addColumn('slug', 'Slug', 'VARCHAR', false, 255, null);
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
        $this->addRelation('User', '\\PromCMS\\Core\\Models\\User', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':role_id',
    1 => ':id',
  ),
), 'SET NULL', 'CASCADE', 'Users', false);
    }

    /**
     *
     * Gets the list of behaviors registered for this table
     *
     * @return array<string, array> Associative array (name => parameters) of behaviors
     */
    public function getBehaviors(): array
    {
        return [
            'sluggable' => ['slug_column' => 'slug', 'slug_pattern' => '', 'replace_pattern' => '/\\W+/', 'replacement' => '-', 'separator' => '-', 'permanent' => 'false', 'scope_column' => '', 'unique_constraint' => 'true'],
            'prom_model' => [],
        ];
    }

    /**
     * Method to invalidate the instance pool of all tables related to prom__user_roles     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool(): void
    {
        // Invalidate objects in related instance pools,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        UserTableMap::clearInstancePool();
    }

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array $row Resultset row.
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return string|null The primary key hash of the row
     */
    public static function getPrimaryKeyHashFromRow(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): ?string
    {
        // If the PK cannot be derived from the row, return NULL.
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return null === $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param array $row Resultset row.
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM)
    {
        return (int) $row[
            $indexType == TableMap::TYPE_NUM
                ? 0 + $offset
                : self::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)
        ];
    }

    /**
     * The class that the tableMap will make instances of.
     *
     * If $withPrefix is true, the returned path
     * uses a dot-path notation which is translated into a path
     * relative to a location on the PHP include_path.
     * (e.g. path.to.MyClass -> 'path/to/MyClass.php')
     *
     * @param bool $withPrefix Whether to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass(bool $withPrefix = true): string
    {
        return $withPrefix ? UserRoleTableMap::CLASS_DEFAULT : UserRoleTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array $row Row returned by DataFetcher->fetch().
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return array (UserRole object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = UserRoleTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = UserRoleTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + UserRoleTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = UserRoleTableMap::OM_CLASS;
            /** @var UserRole $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            UserRoleTableMap::addInstanceToPool($obj, $key);
        }

        return [$obj, $col];
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array<object>
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher): array
    {
        $results = [];

        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = UserRoleTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = UserRoleTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var UserRole $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                UserRoleTableMap::addInstanceToPool($obj, $key);
            } // if key exists
        }

        return $results;
    }
    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param Criteria $criteria Object containing the columns to add.
     * @param string|null $alias Optional table alias
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return void
     */
    public static function addSelectColumns(Criteria $criteria, ?string $alias = null): void
    {
        if (null === $alias) {
            $criteria->addSelectColumn(UserRoleTableMap::COL_ID);
            $criteria->addSelectColumn(UserRoleTableMap::COL_LABEL);
            $criteria->addSelectColumn(UserRoleTableMap::COL_DESCRIPTION);
            $criteria->addSelectColumn(UserRoleTableMap::COL_PERMISSIONS);
            $criteria->addSelectColumn(UserRoleTableMap::COL_SLUG);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.label');
            $criteria->addSelectColumn($alias . '.description');
            $criteria->addSelectColumn($alias . '.permissions');
            $criteria->addSelectColumn($alias . '.slug');
        }
    }

    /**
     * Remove all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be removed as they are only loaded on demand.
     *
     * @param Criteria $criteria Object containing the columns to remove.
     * @param string|null $alias Optional table alias
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return void
     */
    public static function removeSelectColumns(Criteria $criteria, ?string $alias = null): void
    {
        if (null === $alias) {
            $criteria->removeSelectColumn(UserRoleTableMap::COL_ID);
            $criteria->removeSelectColumn(UserRoleTableMap::COL_LABEL);
            $criteria->removeSelectColumn(UserRoleTableMap::COL_DESCRIPTION);
            $criteria->removeSelectColumn(UserRoleTableMap::COL_PERMISSIONS);
            $criteria->removeSelectColumn(UserRoleTableMap::COL_SLUG);
        } else {
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.label');
            $criteria->removeSelectColumn($alias . '.description');
            $criteria->removeSelectColumn($alias . '.permissions');
            $criteria->removeSelectColumn($alias . '.slug');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function getTableMap(): TableMap
    {
        return Propel::getServiceContainer()->getDatabaseMap(UserRoleTableMap::DATABASE_NAME)->getTable(UserRoleTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a UserRole or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or UserRole object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ?ConnectionInterface $con = null): int
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserRoleTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \PromCMS\Core\Models\UserRole) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(UserRoleTableMap::DATABASE_NAME);
            $criteria->add(UserRoleTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = UserRoleQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            UserRoleTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                UserRoleTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the prom__user_roles table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return UserRoleQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a UserRole or Criteria object.
     *
     * @param mixed $criteria Criteria or UserRole object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserRoleTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from UserRole object
        }

        if ($criteria->containsKey(UserRoleTableMap::COL_ID) && $criteria->keyContainsValue(UserRoleTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.UserRoleTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = UserRoleQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
