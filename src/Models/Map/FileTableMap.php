<?php

namespace PromCMS\Core\Models\Map;

use PromCMS\Core\Models\File;
use PromCMS\Core\Models\FileQuery;
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
 * This class defines the structure of the 'prom__files' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class FileTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = '.Map.FileTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'core';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'prom__files';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'File';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\PromCMS\\Core\\Models\\File';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'File';

    /**
     * The total number of columns
     */
    public const NUM_COLUMNS = 10;

    /**
     * The number of lazy-loaded columns
     */
    public const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    public const NUM_HYDRATE_COLUMNS = 10;

    /**
     * the column name for the id field
     */
    public const COL_ID = 'prom__files.id';

    /**
     * the column name for the filename field
     */
    public const COL_FILENAME = 'prom__files.filename';

    /**
     * the column name for the mime_type field
     */
    public const COL_MIME_TYPE = 'prom__files.mime_type';

    /**
     * the column name for the filepath field
     */
    public const COL_FILEPATH = 'prom__files.filepath';

    /**
     * the column name for the private field
     */
    public const COL_PRIVATE = 'prom__files.private';

    /**
     * the column name for the description field
     */
    public const COL_DESCRIPTION = 'prom__files.description';

    /**
     * the column name for the created_by field
     */
    public const COL_CREATED_BY = 'prom__files.created_by';

    /**
     * the column name for the updated_by field
     */
    public const COL_UPDATED_BY = 'prom__files.updated_by';

    /**
     * the column name for the created_at field
     */
    public const COL_CREATED_AT = 'prom__files.created_at';

    /**
     * the column name for the updated_at field
     */
    public const COL_UPDATED_AT = 'prom__files.updated_at';

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
        self::TYPE_PHPNAME       => ['Id', 'Filename', 'MimeType', 'Filepath', 'Private', 'Description', 'CreatedBy', 'UpdatedBy', 'CreatedAt', 'UpdatedAt', ],
        self::TYPE_CAMELNAME     => ['id', 'filename', 'mimeType', 'filepath', 'private', 'description', 'createdBy', 'updatedBy', 'createdAt', 'updatedAt', ],
        self::TYPE_COLNAME       => [FileTableMap::COL_ID, FileTableMap::COL_FILENAME, FileTableMap::COL_MIME_TYPE, FileTableMap::COL_FILEPATH, FileTableMap::COL_PRIVATE, FileTableMap::COL_DESCRIPTION, FileTableMap::COL_CREATED_BY, FileTableMap::COL_UPDATED_BY, FileTableMap::COL_CREATED_AT, FileTableMap::COL_UPDATED_AT, ],
        self::TYPE_FIELDNAME     => ['id', 'filename', 'mime_type', 'filepath', 'private', 'description', 'created_by', 'updated_by', 'created_at', 'updated_at', ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, ]
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'Filename' => 1, 'MimeType' => 2, 'Filepath' => 3, 'Private' => 4, 'Description' => 5, 'CreatedBy' => 6, 'UpdatedBy' => 7, 'CreatedAt' => 8, 'UpdatedAt' => 9, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'filename' => 1, 'mimeType' => 2, 'filepath' => 3, 'private' => 4, 'description' => 5, 'createdBy' => 6, 'updatedBy' => 7, 'createdAt' => 8, 'updatedAt' => 9, ],
        self::TYPE_COLNAME       => [FileTableMap::COL_ID => 0, FileTableMap::COL_FILENAME => 1, FileTableMap::COL_MIME_TYPE => 2, FileTableMap::COL_FILEPATH => 3, FileTableMap::COL_PRIVATE => 4, FileTableMap::COL_DESCRIPTION => 5, FileTableMap::COL_CREATED_BY => 6, FileTableMap::COL_UPDATED_BY => 7, FileTableMap::COL_CREATED_AT => 8, FileTableMap::COL_UPDATED_AT => 9, ],
        self::TYPE_FIELDNAME     => ['id' => 0, 'filename' => 1, 'mime_type' => 2, 'filepath' => 3, 'private' => 4, 'description' => 5, 'created_by' => 6, 'updated_by' => 7, 'created_at' => 8, 'updated_at' => 9, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected $normalizedColumnNameMap = [
        'Id' => 'ID',
        'File.Id' => 'ID',
        'id' => 'ID',
        'file.id' => 'ID',
        'FileTableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'prom__files.id' => 'ID',
        'Filename' => 'FILENAME',
        'File.Filename' => 'FILENAME',
        'filename' => 'FILENAME',
        'file.filename' => 'FILENAME',
        'FileTableMap::COL_FILENAME' => 'FILENAME',
        'COL_FILENAME' => 'FILENAME',
        'prom__files.filename' => 'FILENAME',
        'MimeType' => 'MIME_TYPE',
        'File.MimeType' => 'MIME_TYPE',
        'mimeType' => 'MIME_TYPE',
        'file.mimeType' => 'MIME_TYPE',
        'FileTableMap::COL_MIME_TYPE' => 'MIME_TYPE',
        'COL_MIME_TYPE' => 'MIME_TYPE',
        'mime_type' => 'MIME_TYPE',
        'prom__files.mime_type' => 'MIME_TYPE',
        'Filepath' => 'FILEPATH',
        'File.Filepath' => 'FILEPATH',
        'filepath' => 'FILEPATH',
        'file.filepath' => 'FILEPATH',
        'FileTableMap::COL_FILEPATH' => 'FILEPATH',
        'COL_FILEPATH' => 'FILEPATH',
        'prom__files.filepath' => 'FILEPATH',
        'Private' => 'PRIVATE',
        'File.Private' => 'PRIVATE',
        'private' => 'PRIVATE',
        'file.private' => 'PRIVATE',
        'FileTableMap::COL_PRIVATE' => 'PRIVATE',
        'COL_PRIVATE' => 'PRIVATE',
        'prom__files.private' => 'PRIVATE',
        'Description' => 'DESCRIPTION',
        'File.Description' => 'DESCRIPTION',
        'description' => 'DESCRIPTION',
        'file.description' => 'DESCRIPTION',
        'FileTableMap::COL_DESCRIPTION' => 'DESCRIPTION',
        'COL_DESCRIPTION' => 'DESCRIPTION',
        'prom__files.description' => 'DESCRIPTION',
        'CreatedBy' => 'CREATED_BY',
        'File.CreatedBy' => 'CREATED_BY',
        'createdBy' => 'CREATED_BY',
        'file.createdBy' => 'CREATED_BY',
        'FileTableMap::COL_CREATED_BY' => 'CREATED_BY',
        'COL_CREATED_BY' => 'CREATED_BY',
        'created_by' => 'CREATED_BY',
        'prom__files.created_by' => 'CREATED_BY',
        'UpdatedBy' => 'UPDATED_BY',
        'File.UpdatedBy' => 'UPDATED_BY',
        'updatedBy' => 'UPDATED_BY',
        'file.updatedBy' => 'UPDATED_BY',
        'FileTableMap::COL_UPDATED_BY' => 'UPDATED_BY',
        'COL_UPDATED_BY' => 'UPDATED_BY',
        'updated_by' => 'UPDATED_BY',
        'prom__files.updated_by' => 'UPDATED_BY',
        'CreatedAt' => 'CREATED_AT',
        'File.CreatedAt' => 'CREATED_AT',
        'createdAt' => 'CREATED_AT',
        'file.createdAt' => 'CREATED_AT',
        'FileTableMap::COL_CREATED_AT' => 'CREATED_AT',
        'COL_CREATED_AT' => 'CREATED_AT',
        'created_at' => 'CREATED_AT',
        'prom__files.created_at' => 'CREATED_AT',
        'UpdatedAt' => 'UPDATED_AT',
        'File.UpdatedAt' => 'UPDATED_AT',
        'updatedAt' => 'UPDATED_AT',
        'file.updatedAt' => 'UPDATED_AT',
        'FileTableMap::COL_UPDATED_AT' => 'UPDATED_AT',
        'COL_UPDATED_AT' => 'UPDATED_AT',
        'updated_at' => 'UPDATED_AT',
        'prom__files.updated_at' => 'UPDATED_AT',
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
        $this->setName('prom__files');
        $this->setPhpName('File');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\PromCMS\\Core\\Models\\File');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('filename', 'Filename', 'VARCHAR', true, 255, null);
        $this->addColumn('mime_type', 'MimeType', 'VARCHAR', true, 255, null);
        $this->addColumn('filepath', 'Filepath', 'VARCHAR', true, 255, null);
        $this->addColumn('private', 'Private', 'BOOLEAN', false, null, null);
        $this->addColumn('description', 'Description', 'LONGVARCHAR', false, null, null);
        $this->addForeignKey('created_by', 'CreatedBy', 'INTEGER', 'prom__users', 'id', false, null, null);
        $this->addForeignKey('updated_by', 'UpdatedBy', 'INTEGER', 'prom__users', 'id', false, null, null);
        $this->addColumn('created_at', 'CreatedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('updated_at', 'UpdatedAt', 'TIMESTAMP', false, null, null);
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
        $this->addRelation('UserRelatedByCreatedBy', '\\PromCMS\\Core\\Models\\User', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':created_by',
    1 => ':id',
  ),
), 'CASCADE', 'CASCADE', null, false);
        $this->addRelation('UserRelatedByUpdatedBy', '\\PromCMS\\Core\\Models\\User', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':updated_by',
    1 => ':id',
  ),
), 'CASCADE', 'CASCADE', null, false);
        $this->addRelation('UserRelatedByAvatarId', '\\PromCMS\\Core\\Models\\User', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':avatar_id',
    1 => ':id',
  ),
), 'SET NULL', 'CASCADE', 'UsersRelatedByAvatarId', false);
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
            'timestampable' => ['create_column' => 'created_at', 'update_column' => 'updated_at', 'disable_created_at' => 'false', 'disable_updated_at' => 'false', 'created_at' => 'my_create_date', 'updated_at' => 'my_update_date'],
            'prom_model' => [],
        ];
    }

    /**
     * Method to invalidate the instance pool of all tables related to prom__files     * by a foreign key with ON DELETE CASCADE
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
        return $withPrefix ? FileTableMap::CLASS_DEFAULT : FileTableMap::OM_CLASS;
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
     * @return array (File object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = FileTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = FileTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + FileTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = FileTableMap::OM_CLASS;
            /** @var File $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            FileTableMap::addInstanceToPool($obj, $key);
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
            $key = FileTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = FileTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var File $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                FileTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(FileTableMap::COL_ID);
            $criteria->addSelectColumn(FileTableMap::COL_FILENAME);
            $criteria->addSelectColumn(FileTableMap::COL_MIME_TYPE);
            $criteria->addSelectColumn(FileTableMap::COL_FILEPATH);
            $criteria->addSelectColumn(FileTableMap::COL_PRIVATE);
            $criteria->addSelectColumn(FileTableMap::COL_DESCRIPTION);
            $criteria->addSelectColumn(FileTableMap::COL_CREATED_BY);
            $criteria->addSelectColumn(FileTableMap::COL_UPDATED_BY);
            $criteria->addSelectColumn(FileTableMap::COL_CREATED_AT);
            $criteria->addSelectColumn(FileTableMap::COL_UPDATED_AT);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.filename');
            $criteria->addSelectColumn($alias . '.mime_type');
            $criteria->addSelectColumn($alias . '.filepath');
            $criteria->addSelectColumn($alias . '.private');
            $criteria->addSelectColumn($alias . '.description');
            $criteria->addSelectColumn($alias . '.created_by');
            $criteria->addSelectColumn($alias . '.updated_by');
            $criteria->addSelectColumn($alias . '.created_at');
            $criteria->addSelectColumn($alias . '.updated_at');
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
            $criteria->removeSelectColumn(FileTableMap::COL_ID);
            $criteria->removeSelectColumn(FileTableMap::COL_FILENAME);
            $criteria->removeSelectColumn(FileTableMap::COL_MIME_TYPE);
            $criteria->removeSelectColumn(FileTableMap::COL_FILEPATH);
            $criteria->removeSelectColumn(FileTableMap::COL_PRIVATE);
            $criteria->removeSelectColumn(FileTableMap::COL_DESCRIPTION);
            $criteria->removeSelectColumn(FileTableMap::COL_CREATED_BY);
            $criteria->removeSelectColumn(FileTableMap::COL_UPDATED_BY);
            $criteria->removeSelectColumn(FileTableMap::COL_CREATED_AT);
            $criteria->removeSelectColumn(FileTableMap::COL_UPDATED_AT);
        } else {
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.filename');
            $criteria->removeSelectColumn($alias . '.mime_type');
            $criteria->removeSelectColumn($alias . '.filepath');
            $criteria->removeSelectColumn($alias . '.private');
            $criteria->removeSelectColumn($alias . '.description');
            $criteria->removeSelectColumn($alias . '.created_by');
            $criteria->removeSelectColumn($alias . '.updated_by');
            $criteria->removeSelectColumn($alias . '.created_at');
            $criteria->removeSelectColumn($alias . '.updated_at');
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
        return Propel::getServiceContainer()->getDatabaseMap(FileTableMap::DATABASE_NAME)->getTable(FileTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a File or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or File object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(FileTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \PromCMS\Core\Models\File) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(FileTableMap::DATABASE_NAME);
            $criteria->add(FileTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = FileQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            FileTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                FileTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the prom__files table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return FileQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a File or Criteria object.
     *
     * @param mixed $criteria Criteria or File object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(FileTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from File object
        }

        if ($criteria->containsKey(FileTableMap::COL_ID) && $criteria->keyContainsValue(FileTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.FileTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = FileQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
