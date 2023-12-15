<?php

namespace PromCMS\Core\Models\Base;

use \Exception;
use \PDO;
use PromCMS\Core\Models\File as ChildFile;
use PromCMS\Core\Models\FileQuery as ChildFileQuery;
use PromCMS\Core\Models\Setting as ChildSetting;
use PromCMS\Core\Models\SettingQuery as ChildSettingQuery;
use PromCMS\Core\Models\User as ChildUser;
use PromCMS\Core\Models\UserQuery as ChildUserQuery;
use PromCMS\Core\Models\UserRole as ChildUserRole;
use PromCMS\Core\Models\UserRoleQuery as ChildUserRoleQuery;
use PromCMS\Core\Models\Map\FileTableMap;
use PromCMS\Core\Models\Map\SettingTableMap;
use PromCMS\Core\Models\Map\UserTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;

/**
 * Base class that represents a row from the 'prom__users' table.
 *
 *
 *
 * @package    propel.generator..Base
 */
abstract class User implements ActiveRecordInterface
{
    /**
     * TableMap class name
     *
     * @var string
     */
    public const TABLE_MAP = '\\PromCMS\\Core\\Models\\Map\\UserTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var bool
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var bool
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = [];

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = [];

    /**
     * The value for the id field.
     *
     * @var        int
     */
    protected $id;

    /**
     * The value for the email field.
     *
     * @var        string
     */
    protected $email;

    /**
     * The value for the password field.
     *
     * @var        string
     */
    protected $password;

    /**
     * The value for the firstname field.
     *
     * @var        string
     */
    protected $firstname;

    /**
     * The value for the lastname field.
     *
     * @var        string
     */
    protected $lastname;

    /**
     * The value for the state field.
     *
     * Note: this column has a database default value of: 1
     * @var        int|null
     */
    protected $state;

    /**
     * The value for the avatar_id field.
     *
     * @var        int|null
     */
    protected $avatar_id;

    /**
     * The value for the role_id field.
     *
     * @var        int|null
     */
    protected $role_id;

    /**
     * @var        ChildFile
     */
    protected $aFileRelatedByAvatarId;

    /**
     * @var        ChildUserRole
     */
    protected $aUserRole;

    /**
     * @var        ObjectCollection|ChildFile[] Collection to store aggregation of ChildFile objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildFile> Collection to store aggregation of ChildFile objects.
     */
    protected $collFilesRelatedByCreatedBy;
    protected $collFilesRelatedByCreatedByPartial;

    /**
     * @var        ObjectCollection|ChildFile[] Collection to store aggregation of ChildFile objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildFile> Collection to store aggregation of ChildFile objects.
     */
    protected $collFilesRelatedByUpdatedBy;
    protected $collFilesRelatedByUpdatedByPartial;

    /**
     * @var        ObjectCollection|ChildSetting[] Collection to store aggregation of ChildSetting objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildSetting> Collection to store aggregation of ChildSetting objects.
     */
    protected $collSettingsRelatedByCreatedBy;
    protected $collSettingsRelatedByCreatedByPartial;

    /**
     * @var        ObjectCollection|ChildSetting[] Collection to store aggregation of ChildSetting objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildSetting> Collection to store aggregation of ChildSetting objects.
     */
    protected $collSettingsRelatedByUpdatedBy;
    protected $collSettingsRelatedByUpdatedByPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var bool
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildFile[]
     * @phpstan-var ObjectCollection&\Traversable<ChildFile>
     */
    protected $filesRelatedByCreatedByScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildFile[]
     * @phpstan-var ObjectCollection&\Traversable<ChildFile>
     */
    protected $filesRelatedByUpdatedByScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildSetting[]
     * @phpstan-var ObjectCollection&\Traversable<ChildSetting>
     */
    protected $settingsRelatedByCreatedByScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildSetting[]
     * @phpstan-var ObjectCollection&\Traversable<ChildSetting>
     */
    protected $settingsRelatedByUpdatedByScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see __construct()
     */
    public function applyDefaultValues(): void
    {
        $this->state = 1;
    }

    /**
     * Initializes internal state of PromCMS\Core\Models\Base\User object.
     * @see applyDefaults()
     */
    public function __construct()
    {
        $this->applyDefaultValues();
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return bool True if the object has been modified.
     */
    public function isModified(): bool
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param string $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return bool True if $col has been modified.
     */
    public function isColumnModified(string $col): bool
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns(): array
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return bool True, if the object has never been persisted.
     */
    public function isNew(): bool
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param bool $b the state of the object.
     */
    public function setNew(bool $b): void
    {
        $this->new = $b;
    }

    /**
     * Whether this object has been deleted.
     * @return bool The deleted state of this object.
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param bool $b The deleted state of this object.
     * @return void
     */
    public function setDeleted(bool $b): void
    {
        $this->deleted = $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified(?string $col = null): void
    {
        if (null !== $col) {
            unset($this->modifiedColumns[$col]);
        } else {
            $this->modifiedColumns = [];
        }
    }

    /**
     * Compares this with another <code>User</code> instance.  If
     * <code>obj</code> is an instance of <code>User</code>, delegates to
     * <code>equals(User)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param mixed $obj The object to compare to.
     * @return bool Whether equal to the object specified.
     */
    public function equals($obj): bool
    {
        if (!$obj instanceof static) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey() || null === $obj->getPrimaryKey()) {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns(): array
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param string $name The virtual column name
     * @return bool
     */
    public function hasVirtualColumn(string $name): bool
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param string $name The virtual column name
     * @return mixed
     *
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getVirtualColumn(string $name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of nonexistent virtual column `%s`.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name The virtual column name
     * @param mixed $value The value to give to the virtual column
     *
     * @return $this The current object, for fluid interface
     */
    public function setVirtualColumn(string $name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param string $msg
     * @param int $priority One of the Propel::LOG_* logging levels
     * @return void
     */
    protected function log(string $msg, int $priority = Propel::LOG_INFO): void
    {
        Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param \Propel\Runtime\Parser\AbstractParser|string $parser An AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param bool $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @param string $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME, TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM. Defaults to TableMap::TYPE_PHPNAME.
     * @return string The exported data
     */
    public function exportTo($parser, bool $includeLazyLoadColumns = true, string $keyType = TableMap::TYPE_PHPNAME): string
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray($keyType, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     *
     * @return array<string>
     */
    public function __sleep(): array
    {
        $this->clearAllReferences();

        $cls = new \ReflectionClass($this);
        $propertyNames = [];
        $serializableProperties = array_diff($cls->getProperties(), $cls->getProperties(\ReflectionProperty::IS_STATIC));

        foreach($serializableProperties as $property) {
            $propertyNames[] = $property->getName();
        }

        return $propertyNames;
    }

    /**
     * Get the [id] column value.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the [email] column value.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get the [password] column value.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get the [firstname] column value.
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Get the [lastname] column value.
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Get the [state] column value.
     *
     * @return string|null
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getState()
    {
        if (null === $this->state) {
            return null;
        }
        $valueSet = UserTableMap::getValueSet(UserTableMap::COL_STATE);
        if (!isset($valueSet[$this->state])) {
            throw new PropelException('Unknown stored enum key: ' . $this->state);
        }

        return $valueSet[$this->state];
    }

    /**
     * Get the [avatar_id] column value.
     *
     * @return int|null
     */
    public function getAvatarId()
    {
        return $this->avatar_id;
    }

    /**
     * Get the [role_id] column value.
     *
     * @return int|null
     */
    public function getRoleId()
    {
        return $this->role_id;
    }

    /**
     * Set the value of [id] column.
     *
     * @param int $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[UserTableMap::COL_ID] = true;
        }

        return $this;
    }

    /**
     * Set the value of [email] column.
     *
     * @param string $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setEmail($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->email !== $v) {
            $this->email = $v;
            $this->modifiedColumns[UserTableMap::COL_EMAIL] = true;
        }

        return $this;
    }

    /**
     * Set the value of [password] column.
     *
     * @param string $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setPassword($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->password !== $v) {
            $this->password = $v;
            $this->modifiedColumns[UserTableMap::COL_PASSWORD] = true;
        }

        return $this;
    }

    /**
     * Set the value of [firstname] column.
     *
     * @param string $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setFirstname($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->firstname !== $v) {
            $this->firstname = $v;
            $this->modifiedColumns[UserTableMap::COL_FIRSTNAME] = true;
        }

        return $this;
    }

    /**
     * Set the value of [lastname] column.
     *
     * @param string $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setLastname($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->lastname !== $v) {
            $this->lastname = $v;
            $this->modifiedColumns[UserTableMap::COL_LASTNAME] = true;
        }

        return $this;
    }

    /**
     * Set the value of [state] column.
     *
     * @param string|null $v new value
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setState($v)
    {
        if ($v !== null) {
            $valueSet = UserTableMap::getValueSet(UserTableMap::COL_STATE);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->state !== $v) {
            $this->state = $v;
            $this->modifiedColumns[UserTableMap::COL_STATE] = true;
        }

        return $this;
    }

    /**
     * Set the value of [avatar_id] column.
     *
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setAvatarId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->avatar_id !== $v) {
            $this->avatar_id = $v;
            $this->modifiedColumns[UserTableMap::COL_AVATAR_ID] = true;
        }

        if ($this->aFileRelatedByAvatarId !== null && $this->aFileRelatedByAvatarId->getId() !== $v) {
            $this->aFileRelatedByAvatarId = null;
        }

        return $this;
    }

    /**
     * Set the value of [role_id] column.
     *
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setRoleId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->role_id !== $v) {
            $this->role_id = $v;
            $this->modifiedColumns[UserTableMap::COL_ROLE_ID] = true;
        }

        if ($this->aUserRole !== null && $this->aUserRole->getId() !== $v) {
            $this->aUserRole = null;
        }

        return $this;
    }

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return bool Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues(): bool
    {
            if ($this->state !== 1) {
                return false;
            }

        // otherwise, everything was equal, so return TRUE
        return true;
    }

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array $row The row returned by DataFetcher->fetch().
     * @param int $startcol 0-based offset column which indicates which resultset column to start with.
     * @param bool $rehydrate Whether this object is being re-hydrated from the database.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int next starting column
     * @throws \Propel\Runtime\Exception\PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate(array $row, int $startcol = 0, bool $rehydrate = false, string $indexType = TableMap::TYPE_NUM): int
    {
        try {

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : UserTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : UserTableMap::translateFieldName('Email', TableMap::TYPE_PHPNAME, $indexType)];
            $this->email = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : UserTableMap::translateFieldName('Password', TableMap::TYPE_PHPNAME, $indexType)];
            $this->password = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : UserTableMap::translateFieldName('Firstname', TableMap::TYPE_PHPNAME, $indexType)];
            $this->firstname = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : UserTableMap::translateFieldName('Lastname', TableMap::TYPE_PHPNAME, $indexType)];
            $this->lastname = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : UserTableMap::translateFieldName('State', TableMap::TYPE_PHPNAME, $indexType)];
            $this->state = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : UserTableMap::translateFieldName('AvatarId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->avatar_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 7 + $startcol : UserTableMap::translateFieldName('RoleId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->role_id = (null !== $col) ? (int) $col : null;

            $this->resetModified();
            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 8; // 8 = UserTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\PromCMS\\Core\\Models\\User'), 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws \Propel\Runtime\Exception\PropelException
     * @return void
     */
    public function ensureConsistency(): void
    {
        if ($this->aFileRelatedByAvatarId !== null && $this->avatar_id !== $this->aFileRelatedByAvatarId->getId()) {
            $this->aFileRelatedByAvatarId = null;
        }
        if ($this->aUserRole !== null && $this->role_id !== $this->aUserRole->getId()) {
            $this->aUserRole = null;
        }
    }

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param bool $deep (optional) Whether to also de-associated any related objects.
     * @param ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload(bool $deep = false, ?ConnectionInterface $con = null): void
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(UserTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildUserQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aFileRelatedByAvatarId = null;
            $this->aUserRole = null;
            $this->collFilesRelatedByCreatedBy = null;

            $this->collFilesRelatedByUpdatedBy = null;

            $this->collSettingsRelatedByCreatedBy = null;

            $this->collSettingsRelatedByUpdatedBy = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param ConnectionInterface $con
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     * @see User::setDeleted()
     * @see User::isDeleted()
     */
    public function delete(?ConnectionInterface $con = null): void
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildUserQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $this->setDeleted(true);
            }
        });
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param ConnectionInterface $con
     * @return int The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws \Propel\Runtime\Exception\PropelException
     * @see doSave()
     */
    public function save(?ConnectionInterface $con = null): int
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($this->alreadyInSave) {
            return 0;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserTableMap::DATABASE_NAME);
        }

        return $con->transaction(function () use ($con) {
            $ret = $this->preSave($con);
            $isInsert = $this->isNew();
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                UserTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }

            return $affectedRows;
        });
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param ConnectionInterface $con
     * @return int The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws \Propel\Runtime\Exception\PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con): int
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            // We call the save method on the following object(s) if they
            // were passed to this object by their corresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aFileRelatedByAvatarId !== null) {
                if ($this->aFileRelatedByAvatarId->isModified() || $this->aFileRelatedByAvatarId->isNew()) {
                    $affectedRows += $this->aFileRelatedByAvatarId->save($con);
                }
                $this->setFileRelatedByAvatarId($this->aFileRelatedByAvatarId);
            }

            if ($this->aUserRole !== null) {
                if ($this->aUserRole->isModified() || $this->aUserRole->isNew()) {
                    $affectedRows += $this->aUserRole->save($con);
                }
                $this->setUserRole($this->aUserRole);
            }

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                    $affectedRows += 1;
                } else {
                    $affectedRows += $this->doUpdate($con);
                }
                $this->resetModified();
            }

            if ($this->filesRelatedByCreatedByScheduledForDeletion !== null) {
                if (!$this->filesRelatedByCreatedByScheduledForDeletion->isEmpty()) {
                    \PromCMS\Core\Models\FileQuery::create()
                        ->filterByPrimaryKeys($this->filesRelatedByCreatedByScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->filesRelatedByCreatedByScheduledForDeletion = null;
                }
            }

            if ($this->collFilesRelatedByCreatedBy !== null) {
                foreach ($this->collFilesRelatedByCreatedBy as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->filesRelatedByUpdatedByScheduledForDeletion !== null) {
                if (!$this->filesRelatedByUpdatedByScheduledForDeletion->isEmpty()) {
                    \PromCMS\Core\Models\FileQuery::create()
                        ->filterByPrimaryKeys($this->filesRelatedByUpdatedByScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->filesRelatedByUpdatedByScheduledForDeletion = null;
                }
            }

            if ($this->collFilesRelatedByUpdatedBy !== null) {
                foreach ($this->collFilesRelatedByUpdatedBy as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->settingsRelatedByCreatedByScheduledForDeletion !== null) {
                if (!$this->settingsRelatedByCreatedByScheduledForDeletion->isEmpty()) {
                    \PromCMS\Core\Models\SettingQuery::create()
                        ->filterByPrimaryKeys($this->settingsRelatedByCreatedByScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->settingsRelatedByCreatedByScheduledForDeletion = null;
                }
            }

            if ($this->collSettingsRelatedByCreatedBy !== null) {
                foreach ($this->collSettingsRelatedByCreatedBy as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->settingsRelatedByUpdatedByScheduledForDeletion !== null) {
                if (!$this->settingsRelatedByUpdatedByScheduledForDeletion->isEmpty()) {
                    \PromCMS\Core\Models\SettingQuery::create()
                        ->filterByPrimaryKeys($this->settingsRelatedByUpdatedByScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->settingsRelatedByUpdatedByScheduledForDeletion = null;
                }
            }

            if ($this->collSettingsRelatedByUpdatedBy !== null) {
                foreach ($this->collSettingsRelatedByUpdatedBy as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    }

    /**
     * Insert the row in the database.
     *
     * @param ConnectionInterface $con
     *
     * @throws \Propel\Runtime\Exception\PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con): void
    {
        $modifiedColumns = [];
        $index = 0;

        $this->modifiedColumns[UserTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . UserTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(UserTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(UserTableMap::COL_EMAIL)) {
            $modifiedColumns[':p' . $index++]  = 'email';
        }
        if ($this->isColumnModified(UserTableMap::COL_PASSWORD)) {
            $modifiedColumns[':p' . $index++]  = 'password';
        }
        if ($this->isColumnModified(UserTableMap::COL_FIRSTNAME)) {
            $modifiedColumns[':p' . $index++]  = 'firstname';
        }
        if ($this->isColumnModified(UserTableMap::COL_LASTNAME)) {
            $modifiedColumns[':p' . $index++]  = 'lastname';
        }
        if ($this->isColumnModified(UserTableMap::COL_STATE)) {
            $modifiedColumns[':p' . $index++]  = 'state';
        }
        if ($this->isColumnModified(UserTableMap::COL_AVATAR_ID)) {
            $modifiedColumns[':p' . $index++]  = 'avatar_id';
        }
        if ($this->isColumnModified(UserTableMap::COL_ROLE_ID)) {
            $modifiedColumns[':p' . $index++]  = 'role_id';
        }

        $sql = sprintf(
            'INSERT INTO prom__users (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'id':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);

                        break;
                    case 'email':
                        $stmt->bindValue($identifier, $this->email, PDO::PARAM_STR);

                        break;
                    case 'password':
                        $stmt->bindValue($identifier, $this->password, PDO::PARAM_STR);

                        break;
                    case 'firstname':
                        $stmt->bindValue($identifier, $this->firstname, PDO::PARAM_STR);

                        break;
                    case 'lastname':
                        $stmt->bindValue($identifier, $this->lastname, PDO::PARAM_STR);

                        break;
                    case 'state':
                        $stmt->bindValue($identifier, $this->state, PDO::PARAM_INT);

                        break;
                    case 'avatar_id':
                        $stmt->bindValue($identifier, $this->avatar_id, PDO::PARAM_INT);

                        break;
                    case 'role_id':
                        $stmt->bindValue($identifier, $this->role_id, PDO::PARAM_INT);

                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param ConnectionInterface $con
     *
     * @return int Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con): int
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param string $name name
     * @param string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName(string $name, string $type = TableMap::TYPE_PHPNAME)
    {
        $pos = UserTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param int $pos Position in XML schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition(int $pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();

            case 1:
                return $this->getEmail();

            case 2:
                return $this->getPassword();

            case 3:
                return $this->getFirstname();

            case 4:
                return $this->getLastname();

            case 5:
                return $this->getState();

            case 6:
                return $this->getAvatarId();

            case 7:
                return $this->getRoleId();

            default:
                return null;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param string $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param bool $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param bool $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array An associative array containing the field names (as keys) and field values
     */
    public function toArray(string $keyType = TableMap::TYPE_PHPNAME, bool $includeLazyLoadColumns = true, array $alreadyDumpedObjects = [], bool $includeForeignObjects = false): array
    {
        if (isset($alreadyDumpedObjects['User'][$this->hashCode()])) {
            return ['*RECURSION*'];
        }
        $alreadyDumpedObjects['User'][$this->hashCode()] = true;
        $keys = UserTableMap::getFieldNames($keyType);
        $result = [
            $keys[0] => $this->getId(),
            $keys[1] => $this->getEmail(),
            $keys[2] => $this->getPassword(),
            $keys[3] => $this->getFirstname(),
            $keys[4] => $this->getLastname(),
            $keys[5] => $this->getState(),
            $keys[6] => $this->getAvatarId(),
            $keys[7] => $this->getRoleId(),
        ];
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aFileRelatedByAvatarId) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'file';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'prom__files';
                        break;
                    default:
                        $key = 'File';
                }

                $result[$key] = $this->aFileRelatedByAvatarId->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aUserRole) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'userRole';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'prom__user_roles';
                        break;
                    default:
                        $key = 'UserRole';
                }

                $result[$key] = $this->aUserRole->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collFilesRelatedByCreatedBy) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'files';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'prom__filess';
                        break;
                    default:
                        $key = 'Files';
                }

                $result[$key] = $this->collFilesRelatedByCreatedBy->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collFilesRelatedByUpdatedBy) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'files';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'prom__filess';
                        break;
                    default:
                        $key = 'Files';
                }

                $result[$key] = $this->collFilesRelatedByUpdatedBy->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collSettingsRelatedByCreatedBy) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'settings';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'prom__settingss';
                        break;
                    default:
                        $key = 'Settings';
                }

                $result[$key] = $this->collSettingsRelatedByCreatedBy->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collSettingsRelatedByUpdatedBy) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'settings';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'prom__settingss';
                        break;
                    default:
                        $key = 'Settings';
                }

                $result[$key] = $this->collSettingsRelatedByUpdatedBy->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param string $name
     * @param mixed $value field value
     * @param string $type The type of fieldname the $name is of:
     *                one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                Defaults to TableMap::TYPE_PHPNAME.
     * @return $this
     */
    public function setByName(string $name, $value, string $type = TableMap::TYPE_PHPNAME)
    {
        $pos = UserTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        $this->setByPosition($pos, $value);

        return $this;
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param int $pos position in xml schema
     * @param mixed $value field value
     * @return $this
     */
    public function setByPosition(int $pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setEmail($value);
                break;
            case 2:
                $this->setPassword($value);
                break;
            case 3:
                $this->setFirstname($value);
                break;
            case 4:
                $this->setLastname($value);
                break;
            case 5:
                $valueSet = UserTableMap::getValueSet(UserTableMap::COL_STATE);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setState($value);
                break;
            case 6:
                $this->setAvatarId($value);
                break;
            case 7:
                $this->setRoleId($value);
                break;
        } // switch()

        return $this;
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param array $arr An array to populate the object from.
     * @param string $keyType The type of keys the array uses.
     * @return $this
     */
    public function fromArray(array $arr, string $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = UserTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setEmail($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setPassword($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setFirstname($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setLastname($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setState($arr[$keys[5]]);
        }
        if (array_key_exists($keys[6], $arr)) {
            $this->setAvatarId($arr[$keys[6]]);
        }
        if (array_key_exists($keys[7], $arr)) {
            $this->setRoleId($arr[$keys[7]]);
        }

        return $this;
    }

     /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     * @param string $keyType The type of keys the array uses.
     *
     * @return $this The current object, for fluid interface
     */
    public function importFrom($parser, string $data, string $keyType = TableMap::TYPE_PHPNAME)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), $keyType);

        return $this;
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return \Propel\Runtime\ActiveQuery\Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria(): Criteria
    {
        $criteria = new Criteria(UserTableMap::DATABASE_NAME);

        if ($this->isColumnModified(UserTableMap::COL_ID)) {
            $criteria->add(UserTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(UserTableMap::COL_EMAIL)) {
            $criteria->add(UserTableMap::COL_EMAIL, $this->email);
        }
        if ($this->isColumnModified(UserTableMap::COL_PASSWORD)) {
            $criteria->add(UserTableMap::COL_PASSWORD, $this->password);
        }
        if ($this->isColumnModified(UserTableMap::COL_FIRSTNAME)) {
            $criteria->add(UserTableMap::COL_FIRSTNAME, $this->firstname);
        }
        if ($this->isColumnModified(UserTableMap::COL_LASTNAME)) {
            $criteria->add(UserTableMap::COL_LASTNAME, $this->lastname);
        }
        if ($this->isColumnModified(UserTableMap::COL_STATE)) {
            $criteria->add(UserTableMap::COL_STATE, $this->state);
        }
        if ($this->isColumnModified(UserTableMap::COL_AVATAR_ID)) {
            $criteria->add(UserTableMap::COL_AVATAR_ID, $this->avatar_id);
        }
        if ($this->isColumnModified(UserTableMap::COL_ROLE_ID)) {
            $criteria->add(UserTableMap::COL_ROLE_ID, $this->role_id);
        }

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether they have been modified.
     *
     * @throws LogicException if no primary key is defined
     *
     * @return \Propel\Runtime\ActiveQuery\Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria(): Criteria
    {
        $criteria = ChildUserQuery::create();
        $criteria->add(UserTableMap::COL_ID, $this->id);

        return $criteria;
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int|string Hashcode
     */
    public function hashCode()
    {
        $validPk = null !== $this->getId();

        $validPrimaryKeyFKs = 0;
        $primaryKeyFKs = [];

        if ($validPk) {
            return crc32(json_encode($this->getPrimaryKey(), JSON_UNESCAPED_UNICODE));
        } elseif ($validPrimaryKeyFKs) {
            return crc32(json_encode($primaryKeyFKs, JSON_UNESCAPED_UNICODE));
        }

        return spl_object_hash($this);
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param int|null $key Primary key.
     * @return void
     */
    public function setPrimaryKey(?int $key = null): void
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     *
     * @return bool
     */
    public function isPrimaryKeyNull(): bool
    {
        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of \PromCMS\Core\Models\User (or compatible) type.
     * @param bool $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param bool $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws \Propel\Runtime\Exception\PropelException
     * @return void
     */
    public function copyInto(object $copyObj, bool $deepCopy = false, bool $makeNew = true): void
    {
        $copyObj->setEmail($this->getEmail());
        $copyObj->setPassword($this->getPassword());
        $copyObj->setFirstname($this->getFirstname());
        $copyObj->setLastname($this->getLastname());
        $copyObj->setState($this->getState());
        $copyObj->setAvatarId($this->getAvatarId());
        $copyObj->setRoleId($this->getRoleId());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getFilesRelatedByCreatedBy() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addFileRelatedByCreatedBy($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getFilesRelatedByUpdatedBy() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addFileRelatedByUpdatedBy($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getSettingsRelatedByCreatedBy() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addSettingRelatedByCreatedBy($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getSettingsRelatedByUpdatedBy() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addSettingRelatedByUpdatedBy($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param bool $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return \PromCMS\Core\Models\User Clone of current object.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function copy(bool $deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }

    /**
     * Declares an association between this object and a ChildFile object.
     *
     * @param ChildFile|null $v
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setFileRelatedByAvatarId(ChildFile $v = null)
    {
        if ($v === null) {
            $this->setAvatarId(NULL);
        } else {
            $this->setAvatarId($v->getId());
        }

        $this->aFileRelatedByAvatarId = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildFile object, it will not be re-added.
        if ($v !== null) {
            $v->addUserRelatedByAvatarId($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildFile object
     *
     * @param ConnectionInterface $con Optional Connection object.
     * @return ChildFile|null The associated ChildFile object.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getFileRelatedByAvatarId(?ConnectionInterface $con = null)
    {
        if ($this->aFileRelatedByAvatarId === null && ($this->avatar_id != 0)) {
            $this->aFileRelatedByAvatarId = ChildFileQuery::create()->findPk($this->avatar_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aFileRelatedByAvatarId->addUsersRelatedByAvatarId($this);
             */
        }

        return $this->aFileRelatedByAvatarId;
    }

    /**
     * Declares an association between this object and a ChildUserRole object.
     *
     * @param ChildUserRole|null $v
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setUserRole(ChildUserRole $v = null)
    {
        if ($v === null) {
            $this->setRoleId(NULL);
        } else {
            $this->setRoleId($v->getId());
        }

        $this->aUserRole = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildUserRole object, it will not be re-added.
        if ($v !== null) {
            $v->addUser($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildUserRole object
     *
     * @param ConnectionInterface $con Optional Connection object.
     * @return ChildUserRole|null The associated ChildUserRole object.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getUserRole(?ConnectionInterface $con = null)
    {
        if ($this->aUserRole === null && ($this->role_id != 0)) {
            $this->aUserRole = ChildUserRoleQuery::create()->findPk($this->role_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aUserRole->addUsers($this);
             */
        }

        return $this->aUserRole;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName): void
    {
        if ('FileRelatedByCreatedBy' === $relationName) {
            $this->initFilesRelatedByCreatedBy();
            return;
        }
        if ('FileRelatedByUpdatedBy' === $relationName) {
            $this->initFilesRelatedByUpdatedBy();
            return;
        }
        if ('SettingRelatedByCreatedBy' === $relationName) {
            $this->initSettingsRelatedByCreatedBy();
            return;
        }
        if ('SettingRelatedByUpdatedBy' === $relationName) {
            $this->initSettingsRelatedByUpdatedBy();
            return;
        }
    }

    /**
     * Clears out the collFilesRelatedByCreatedBy collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addFilesRelatedByCreatedBy()
     */
    public function clearFilesRelatedByCreatedBy()
    {
        $this->collFilesRelatedByCreatedBy = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collFilesRelatedByCreatedBy collection loaded partially.
     *
     * @return void
     */
    public function resetPartialFilesRelatedByCreatedBy($v = true): void
    {
        $this->collFilesRelatedByCreatedByPartial = $v;
    }

    /**
     * Initializes the collFilesRelatedByCreatedBy collection.
     *
     * By default this just sets the collFilesRelatedByCreatedBy collection to an empty array (like clearcollFilesRelatedByCreatedBy());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initFilesRelatedByCreatedBy(bool $overrideExisting = true): void
    {
        if (null !== $this->collFilesRelatedByCreatedBy && !$overrideExisting) {
            return;
        }

        $collectionClassName = FileTableMap::getTableMap()->getCollectionClassName();

        $this->collFilesRelatedByCreatedBy = new $collectionClassName;
        $this->collFilesRelatedByCreatedBy->setModel('\PromCMS\Core\Models\File');
    }

    /**
     * Gets an array of ChildFile objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildUser is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildFile[] List of ChildFile objects
     * @phpstan-return ObjectCollection&\Traversable<ChildFile> List of ChildFile objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getFilesRelatedByCreatedBy(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collFilesRelatedByCreatedByPartial && !$this->isNew();
        if (null === $this->collFilesRelatedByCreatedBy || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collFilesRelatedByCreatedBy) {
                    $this->initFilesRelatedByCreatedBy();
                } else {
                    $collectionClassName = FileTableMap::getTableMap()->getCollectionClassName();

                    $collFilesRelatedByCreatedBy = new $collectionClassName;
                    $collFilesRelatedByCreatedBy->setModel('\PromCMS\Core\Models\File');

                    return $collFilesRelatedByCreatedBy;
                }
            } else {
                $collFilesRelatedByCreatedBy = ChildFileQuery::create(null, $criteria)
                    ->filterByUserRelatedByCreatedBy($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collFilesRelatedByCreatedByPartial && count($collFilesRelatedByCreatedBy)) {
                        $this->initFilesRelatedByCreatedBy(false);

                        foreach ($collFilesRelatedByCreatedBy as $obj) {
                            if (false == $this->collFilesRelatedByCreatedBy->contains($obj)) {
                                $this->collFilesRelatedByCreatedBy->append($obj);
                            }
                        }

                        $this->collFilesRelatedByCreatedByPartial = true;
                    }

                    return $collFilesRelatedByCreatedBy;
                }

                if ($partial && $this->collFilesRelatedByCreatedBy) {
                    foreach ($this->collFilesRelatedByCreatedBy as $obj) {
                        if ($obj->isNew()) {
                            $collFilesRelatedByCreatedBy[] = $obj;
                        }
                    }
                }

                $this->collFilesRelatedByCreatedBy = $collFilesRelatedByCreatedBy;
                $this->collFilesRelatedByCreatedByPartial = false;
            }
        }

        return $this->collFilesRelatedByCreatedBy;
    }

    /**
     * Sets a collection of ChildFile objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $filesRelatedByCreatedBy A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setFilesRelatedByCreatedBy(Collection $filesRelatedByCreatedBy, ?ConnectionInterface $con = null)
    {
        /** @var ChildFile[] $filesRelatedByCreatedByToDelete */
        $filesRelatedByCreatedByToDelete = $this->getFilesRelatedByCreatedBy(new Criteria(), $con)->diff($filesRelatedByCreatedBy);


        $this->filesRelatedByCreatedByScheduledForDeletion = $filesRelatedByCreatedByToDelete;

        foreach ($filesRelatedByCreatedByToDelete as $fileRelatedByCreatedByRemoved) {
            $fileRelatedByCreatedByRemoved->setUserRelatedByCreatedBy(null);
        }

        $this->collFilesRelatedByCreatedBy = null;
        foreach ($filesRelatedByCreatedBy as $fileRelatedByCreatedBy) {
            $this->addFileRelatedByCreatedBy($fileRelatedByCreatedBy);
        }

        $this->collFilesRelatedByCreatedBy = $filesRelatedByCreatedBy;
        $this->collFilesRelatedByCreatedByPartial = false;

        return $this;
    }

    /**
     * Returns the number of related File objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related File objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countFilesRelatedByCreatedBy(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collFilesRelatedByCreatedByPartial && !$this->isNew();
        if (null === $this->collFilesRelatedByCreatedBy || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collFilesRelatedByCreatedBy) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getFilesRelatedByCreatedBy());
            }

            $query = ChildFileQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByUserRelatedByCreatedBy($this)
                ->count($con);
        }

        return count($this->collFilesRelatedByCreatedBy);
    }

    /**
     * Method called to associate a ChildFile object to this object
     * through the ChildFile foreign key attribute.
     *
     * @param ChildFile $l ChildFile
     * @return $this The current object (for fluent API support)
     */
    public function addFileRelatedByCreatedBy(ChildFile $l)
    {
        if ($this->collFilesRelatedByCreatedBy === null) {
            $this->initFilesRelatedByCreatedBy();
            $this->collFilesRelatedByCreatedByPartial = true;
        }

        if (!$this->collFilesRelatedByCreatedBy->contains($l)) {
            $this->doAddFileRelatedByCreatedBy($l);

            if ($this->filesRelatedByCreatedByScheduledForDeletion and $this->filesRelatedByCreatedByScheduledForDeletion->contains($l)) {
                $this->filesRelatedByCreatedByScheduledForDeletion->remove($this->filesRelatedByCreatedByScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildFile $fileRelatedByCreatedBy The ChildFile object to add.
     */
    protected function doAddFileRelatedByCreatedBy(ChildFile $fileRelatedByCreatedBy): void
    {
        $this->collFilesRelatedByCreatedBy[]= $fileRelatedByCreatedBy;
        $fileRelatedByCreatedBy->setUserRelatedByCreatedBy($this);
    }

    /**
     * @param ChildFile $fileRelatedByCreatedBy The ChildFile object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeFileRelatedByCreatedBy(ChildFile $fileRelatedByCreatedBy)
    {
        if ($this->getFilesRelatedByCreatedBy()->contains($fileRelatedByCreatedBy)) {
            $pos = $this->collFilesRelatedByCreatedBy->search($fileRelatedByCreatedBy);
            $this->collFilesRelatedByCreatedBy->remove($pos);
            if (null === $this->filesRelatedByCreatedByScheduledForDeletion) {
                $this->filesRelatedByCreatedByScheduledForDeletion = clone $this->collFilesRelatedByCreatedBy;
                $this->filesRelatedByCreatedByScheduledForDeletion->clear();
            }
            $this->filesRelatedByCreatedByScheduledForDeletion[]= $fileRelatedByCreatedBy;
            $fileRelatedByCreatedBy->setUserRelatedByCreatedBy(null);
        }

        return $this;
    }

    /**
     * Clears out the collFilesRelatedByUpdatedBy collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addFilesRelatedByUpdatedBy()
     */
    public function clearFilesRelatedByUpdatedBy()
    {
        $this->collFilesRelatedByUpdatedBy = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collFilesRelatedByUpdatedBy collection loaded partially.
     *
     * @return void
     */
    public function resetPartialFilesRelatedByUpdatedBy($v = true): void
    {
        $this->collFilesRelatedByUpdatedByPartial = $v;
    }

    /**
     * Initializes the collFilesRelatedByUpdatedBy collection.
     *
     * By default this just sets the collFilesRelatedByUpdatedBy collection to an empty array (like clearcollFilesRelatedByUpdatedBy());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initFilesRelatedByUpdatedBy(bool $overrideExisting = true): void
    {
        if (null !== $this->collFilesRelatedByUpdatedBy && !$overrideExisting) {
            return;
        }

        $collectionClassName = FileTableMap::getTableMap()->getCollectionClassName();

        $this->collFilesRelatedByUpdatedBy = new $collectionClassName;
        $this->collFilesRelatedByUpdatedBy->setModel('\PromCMS\Core\Models\File');
    }

    /**
     * Gets an array of ChildFile objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildUser is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildFile[] List of ChildFile objects
     * @phpstan-return ObjectCollection&\Traversable<ChildFile> List of ChildFile objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getFilesRelatedByUpdatedBy(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collFilesRelatedByUpdatedByPartial && !$this->isNew();
        if (null === $this->collFilesRelatedByUpdatedBy || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collFilesRelatedByUpdatedBy) {
                    $this->initFilesRelatedByUpdatedBy();
                } else {
                    $collectionClassName = FileTableMap::getTableMap()->getCollectionClassName();

                    $collFilesRelatedByUpdatedBy = new $collectionClassName;
                    $collFilesRelatedByUpdatedBy->setModel('\PromCMS\Core\Models\File');

                    return $collFilesRelatedByUpdatedBy;
                }
            } else {
                $collFilesRelatedByUpdatedBy = ChildFileQuery::create(null, $criteria)
                    ->filterByUserRelatedByUpdatedBy($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collFilesRelatedByUpdatedByPartial && count($collFilesRelatedByUpdatedBy)) {
                        $this->initFilesRelatedByUpdatedBy(false);

                        foreach ($collFilesRelatedByUpdatedBy as $obj) {
                            if (false == $this->collFilesRelatedByUpdatedBy->contains($obj)) {
                                $this->collFilesRelatedByUpdatedBy->append($obj);
                            }
                        }

                        $this->collFilesRelatedByUpdatedByPartial = true;
                    }

                    return $collFilesRelatedByUpdatedBy;
                }

                if ($partial && $this->collFilesRelatedByUpdatedBy) {
                    foreach ($this->collFilesRelatedByUpdatedBy as $obj) {
                        if ($obj->isNew()) {
                            $collFilesRelatedByUpdatedBy[] = $obj;
                        }
                    }
                }

                $this->collFilesRelatedByUpdatedBy = $collFilesRelatedByUpdatedBy;
                $this->collFilesRelatedByUpdatedByPartial = false;
            }
        }

        return $this->collFilesRelatedByUpdatedBy;
    }

    /**
     * Sets a collection of ChildFile objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $filesRelatedByUpdatedBy A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setFilesRelatedByUpdatedBy(Collection $filesRelatedByUpdatedBy, ?ConnectionInterface $con = null)
    {
        /** @var ChildFile[] $filesRelatedByUpdatedByToDelete */
        $filesRelatedByUpdatedByToDelete = $this->getFilesRelatedByUpdatedBy(new Criteria(), $con)->diff($filesRelatedByUpdatedBy);


        $this->filesRelatedByUpdatedByScheduledForDeletion = $filesRelatedByUpdatedByToDelete;

        foreach ($filesRelatedByUpdatedByToDelete as $fileRelatedByUpdatedByRemoved) {
            $fileRelatedByUpdatedByRemoved->setUserRelatedByUpdatedBy(null);
        }

        $this->collFilesRelatedByUpdatedBy = null;
        foreach ($filesRelatedByUpdatedBy as $fileRelatedByUpdatedBy) {
            $this->addFileRelatedByUpdatedBy($fileRelatedByUpdatedBy);
        }

        $this->collFilesRelatedByUpdatedBy = $filesRelatedByUpdatedBy;
        $this->collFilesRelatedByUpdatedByPartial = false;

        return $this;
    }

    /**
     * Returns the number of related File objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related File objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countFilesRelatedByUpdatedBy(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collFilesRelatedByUpdatedByPartial && !$this->isNew();
        if (null === $this->collFilesRelatedByUpdatedBy || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collFilesRelatedByUpdatedBy) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getFilesRelatedByUpdatedBy());
            }

            $query = ChildFileQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByUserRelatedByUpdatedBy($this)
                ->count($con);
        }

        return count($this->collFilesRelatedByUpdatedBy);
    }

    /**
     * Method called to associate a ChildFile object to this object
     * through the ChildFile foreign key attribute.
     *
     * @param ChildFile $l ChildFile
     * @return $this The current object (for fluent API support)
     */
    public function addFileRelatedByUpdatedBy(ChildFile $l)
    {
        if ($this->collFilesRelatedByUpdatedBy === null) {
            $this->initFilesRelatedByUpdatedBy();
            $this->collFilesRelatedByUpdatedByPartial = true;
        }

        if (!$this->collFilesRelatedByUpdatedBy->contains($l)) {
            $this->doAddFileRelatedByUpdatedBy($l);

            if ($this->filesRelatedByUpdatedByScheduledForDeletion and $this->filesRelatedByUpdatedByScheduledForDeletion->contains($l)) {
                $this->filesRelatedByUpdatedByScheduledForDeletion->remove($this->filesRelatedByUpdatedByScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildFile $fileRelatedByUpdatedBy The ChildFile object to add.
     */
    protected function doAddFileRelatedByUpdatedBy(ChildFile $fileRelatedByUpdatedBy): void
    {
        $this->collFilesRelatedByUpdatedBy[]= $fileRelatedByUpdatedBy;
        $fileRelatedByUpdatedBy->setUserRelatedByUpdatedBy($this);
    }

    /**
     * @param ChildFile $fileRelatedByUpdatedBy The ChildFile object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeFileRelatedByUpdatedBy(ChildFile $fileRelatedByUpdatedBy)
    {
        if ($this->getFilesRelatedByUpdatedBy()->contains($fileRelatedByUpdatedBy)) {
            $pos = $this->collFilesRelatedByUpdatedBy->search($fileRelatedByUpdatedBy);
            $this->collFilesRelatedByUpdatedBy->remove($pos);
            if (null === $this->filesRelatedByUpdatedByScheduledForDeletion) {
                $this->filesRelatedByUpdatedByScheduledForDeletion = clone $this->collFilesRelatedByUpdatedBy;
                $this->filesRelatedByUpdatedByScheduledForDeletion->clear();
            }
            $this->filesRelatedByUpdatedByScheduledForDeletion[]= $fileRelatedByUpdatedBy;
            $fileRelatedByUpdatedBy->setUserRelatedByUpdatedBy(null);
        }

        return $this;
    }

    /**
     * Clears out the collSettingsRelatedByCreatedBy collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addSettingsRelatedByCreatedBy()
     */
    public function clearSettingsRelatedByCreatedBy()
    {
        $this->collSettingsRelatedByCreatedBy = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collSettingsRelatedByCreatedBy collection loaded partially.
     *
     * @return void
     */
    public function resetPartialSettingsRelatedByCreatedBy($v = true): void
    {
        $this->collSettingsRelatedByCreatedByPartial = $v;
    }

    /**
     * Initializes the collSettingsRelatedByCreatedBy collection.
     *
     * By default this just sets the collSettingsRelatedByCreatedBy collection to an empty array (like clearcollSettingsRelatedByCreatedBy());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initSettingsRelatedByCreatedBy(bool $overrideExisting = true): void
    {
        if (null !== $this->collSettingsRelatedByCreatedBy && !$overrideExisting) {
            return;
        }

        $collectionClassName = SettingTableMap::getTableMap()->getCollectionClassName();

        $this->collSettingsRelatedByCreatedBy = new $collectionClassName;
        $this->collSettingsRelatedByCreatedBy->setModel('\PromCMS\Core\Models\Setting');
    }

    /**
     * Gets an array of ChildSetting objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildUser is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildSetting[] List of ChildSetting objects
     * @phpstan-return ObjectCollection&\Traversable<ChildSetting> List of ChildSetting objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getSettingsRelatedByCreatedBy(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collSettingsRelatedByCreatedByPartial && !$this->isNew();
        if (null === $this->collSettingsRelatedByCreatedBy || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collSettingsRelatedByCreatedBy) {
                    $this->initSettingsRelatedByCreatedBy();
                } else {
                    $collectionClassName = SettingTableMap::getTableMap()->getCollectionClassName();

                    $collSettingsRelatedByCreatedBy = new $collectionClassName;
                    $collSettingsRelatedByCreatedBy->setModel('\PromCMS\Core\Models\Setting');

                    return $collSettingsRelatedByCreatedBy;
                }
            } else {
                $collSettingsRelatedByCreatedBy = ChildSettingQuery::create(null, $criteria)
                    ->filterByUserRelatedByCreatedBy($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collSettingsRelatedByCreatedByPartial && count($collSettingsRelatedByCreatedBy)) {
                        $this->initSettingsRelatedByCreatedBy(false);

                        foreach ($collSettingsRelatedByCreatedBy as $obj) {
                            if (false == $this->collSettingsRelatedByCreatedBy->contains($obj)) {
                                $this->collSettingsRelatedByCreatedBy->append($obj);
                            }
                        }

                        $this->collSettingsRelatedByCreatedByPartial = true;
                    }

                    return $collSettingsRelatedByCreatedBy;
                }

                if ($partial && $this->collSettingsRelatedByCreatedBy) {
                    foreach ($this->collSettingsRelatedByCreatedBy as $obj) {
                        if ($obj->isNew()) {
                            $collSettingsRelatedByCreatedBy[] = $obj;
                        }
                    }
                }

                $this->collSettingsRelatedByCreatedBy = $collSettingsRelatedByCreatedBy;
                $this->collSettingsRelatedByCreatedByPartial = false;
            }
        }

        return $this->collSettingsRelatedByCreatedBy;
    }

    /**
     * Sets a collection of ChildSetting objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $settingsRelatedByCreatedBy A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setSettingsRelatedByCreatedBy(Collection $settingsRelatedByCreatedBy, ?ConnectionInterface $con = null)
    {
        /** @var ChildSetting[] $settingsRelatedByCreatedByToDelete */
        $settingsRelatedByCreatedByToDelete = $this->getSettingsRelatedByCreatedBy(new Criteria(), $con)->diff($settingsRelatedByCreatedBy);


        $this->settingsRelatedByCreatedByScheduledForDeletion = $settingsRelatedByCreatedByToDelete;

        foreach ($settingsRelatedByCreatedByToDelete as $settingRelatedByCreatedByRemoved) {
            $settingRelatedByCreatedByRemoved->setUserRelatedByCreatedBy(null);
        }

        $this->collSettingsRelatedByCreatedBy = null;
        foreach ($settingsRelatedByCreatedBy as $settingRelatedByCreatedBy) {
            $this->addSettingRelatedByCreatedBy($settingRelatedByCreatedBy);
        }

        $this->collSettingsRelatedByCreatedBy = $settingsRelatedByCreatedBy;
        $this->collSettingsRelatedByCreatedByPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Setting objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related Setting objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countSettingsRelatedByCreatedBy(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collSettingsRelatedByCreatedByPartial && !$this->isNew();
        if (null === $this->collSettingsRelatedByCreatedBy || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collSettingsRelatedByCreatedBy) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getSettingsRelatedByCreatedBy());
            }

            $query = ChildSettingQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByUserRelatedByCreatedBy($this)
                ->count($con);
        }

        return count($this->collSettingsRelatedByCreatedBy);
    }

    /**
     * Method called to associate a ChildSetting object to this object
     * through the ChildSetting foreign key attribute.
     *
     * @param ChildSetting $l ChildSetting
     * @return $this The current object (for fluent API support)
     */
    public function addSettingRelatedByCreatedBy(ChildSetting $l)
    {
        if ($this->collSettingsRelatedByCreatedBy === null) {
            $this->initSettingsRelatedByCreatedBy();
            $this->collSettingsRelatedByCreatedByPartial = true;
        }

        if (!$this->collSettingsRelatedByCreatedBy->contains($l)) {
            $this->doAddSettingRelatedByCreatedBy($l);

            if ($this->settingsRelatedByCreatedByScheduledForDeletion and $this->settingsRelatedByCreatedByScheduledForDeletion->contains($l)) {
                $this->settingsRelatedByCreatedByScheduledForDeletion->remove($this->settingsRelatedByCreatedByScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildSetting $settingRelatedByCreatedBy The ChildSetting object to add.
     */
    protected function doAddSettingRelatedByCreatedBy(ChildSetting $settingRelatedByCreatedBy): void
    {
        $this->collSettingsRelatedByCreatedBy[]= $settingRelatedByCreatedBy;
        $settingRelatedByCreatedBy->setUserRelatedByCreatedBy($this);
    }

    /**
     * @param ChildSetting $settingRelatedByCreatedBy The ChildSetting object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeSettingRelatedByCreatedBy(ChildSetting $settingRelatedByCreatedBy)
    {
        if ($this->getSettingsRelatedByCreatedBy()->contains($settingRelatedByCreatedBy)) {
            $pos = $this->collSettingsRelatedByCreatedBy->search($settingRelatedByCreatedBy);
            $this->collSettingsRelatedByCreatedBy->remove($pos);
            if (null === $this->settingsRelatedByCreatedByScheduledForDeletion) {
                $this->settingsRelatedByCreatedByScheduledForDeletion = clone $this->collSettingsRelatedByCreatedBy;
                $this->settingsRelatedByCreatedByScheduledForDeletion->clear();
            }
            $this->settingsRelatedByCreatedByScheduledForDeletion[]= $settingRelatedByCreatedBy;
            $settingRelatedByCreatedBy->setUserRelatedByCreatedBy(null);
        }

        return $this;
    }

    /**
     * Clears out the collSettingsRelatedByUpdatedBy collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addSettingsRelatedByUpdatedBy()
     */
    public function clearSettingsRelatedByUpdatedBy()
    {
        $this->collSettingsRelatedByUpdatedBy = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collSettingsRelatedByUpdatedBy collection loaded partially.
     *
     * @return void
     */
    public function resetPartialSettingsRelatedByUpdatedBy($v = true): void
    {
        $this->collSettingsRelatedByUpdatedByPartial = $v;
    }

    /**
     * Initializes the collSettingsRelatedByUpdatedBy collection.
     *
     * By default this just sets the collSettingsRelatedByUpdatedBy collection to an empty array (like clearcollSettingsRelatedByUpdatedBy());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initSettingsRelatedByUpdatedBy(bool $overrideExisting = true): void
    {
        if (null !== $this->collSettingsRelatedByUpdatedBy && !$overrideExisting) {
            return;
        }

        $collectionClassName = SettingTableMap::getTableMap()->getCollectionClassName();

        $this->collSettingsRelatedByUpdatedBy = new $collectionClassName;
        $this->collSettingsRelatedByUpdatedBy->setModel('\PromCMS\Core\Models\Setting');
    }

    /**
     * Gets an array of ChildSetting objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildUser is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildSetting[] List of ChildSetting objects
     * @phpstan-return ObjectCollection&\Traversable<ChildSetting> List of ChildSetting objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getSettingsRelatedByUpdatedBy(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collSettingsRelatedByUpdatedByPartial && !$this->isNew();
        if (null === $this->collSettingsRelatedByUpdatedBy || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collSettingsRelatedByUpdatedBy) {
                    $this->initSettingsRelatedByUpdatedBy();
                } else {
                    $collectionClassName = SettingTableMap::getTableMap()->getCollectionClassName();

                    $collSettingsRelatedByUpdatedBy = new $collectionClassName;
                    $collSettingsRelatedByUpdatedBy->setModel('\PromCMS\Core\Models\Setting');

                    return $collSettingsRelatedByUpdatedBy;
                }
            } else {
                $collSettingsRelatedByUpdatedBy = ChildSettingQuery::create(null, $criteria)
                    ->filterByUserRelatedByUpdatedBy($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collSettingsRelatedByUpdatedByPartial && count($collSettingsRelatedByUpdatedBy)) {
                        $this->initSettingsRelatedByUpdatedBy(false);

                        foreach ($collSettingsRelatedByUpdatedBy as $obj) {
                            if (false == $this->collSettingsRelatedByUpdatedBy->contains($obj)) {
                                $this->collSettingsRelatedByUpdatedBy->append($obj);
                            }
                        }

                        $this->collSettingsRelatedByUpdatedByPartial = true;
                    }

                    return $collSettingsRelatedByUpdatedBy;
                }

                if ($partial && $this->collSettingsRelatedByUpdatedBy) {
                    foreach ($this->collSettingsRelatedByUpdatedBy as $obj) {
                        if ($obj->isNew()) {
                            $collSettingsRelatedByUpdatedBy[] = $obj;
                        }
                    }
                }

                $this->collSettingsRelatedByUpdatedBy = $collSettingsRelatedByUpdatedBy;
                $this->collSettingsRelatedByUpdatedByPartial = false;
            }
        }

        return $this->collSettingsRelatedByUpdatedBy;
    }

    /**
     * Sets a collection of ChildSetting objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $settingsRelatedByUpdatedBy A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setSettingsRelatedByUpdatedBy(Collection $settingsRelatedByUpdatedBy, ?ConnectionInterface $con = null)
    {
        /** @var ChildSetting[] $settingsRelatedByUpdatedByToDelete */
        $settingsRelatedByUpdatedByToDelete = $this->getSettingsRelatedByUpdatedBy(new Criteria(), $con)->diff($settingsRelatedByUpdatedBy);


        $this->settingsRelatedByUpdatedByScheduledForDeletion = $settingsRelatedByUpdatedByToDelete;

        foreach ($settingsRelatedByUpdatedByToDelete as $settingRelatedByUpdatedByRemoved) {
            $settingRelatedByUpdatedByRemoved->setUserRelatedByUpdatedBy(null);
        }

        $this->collSettingsRelatedByUpdatedBy = null;
        foreach ($settingsRelatedByUpdatedBy as $settingRelatedByUpdatedBy) {
            $this->addSettingRelatedByUpdatedBy($settingRelatedByUpdatedBy);
        }

        $this->collSettingsRelatedByUpdatedBy = $settingsRelatedByUpdatedBy;
        $this->collSettingsRelatedByUpdatedByPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Setting objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related Setting objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countSettingsRelatedByUpdatedBy(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collSettingsRelatedByUpdatedByPartial && !$this->isNew();
        if (null === $this->collSettingsRelatedByUpdatedBy || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collSettingsRelatedByUpdatedBy) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getSettingsRelatedByUpdatedBy());
            }

            $query = ChildSettingQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByUserRelatedByUpdatedBy($this)
                ->count($con);
        }

        return count($this->collSettingsRelatedByUpdatedBy);
    }

    /**
     * Method called to associate a ChildSetting object to this object
     * through the ChildSetting foreign key attribute.
     *
     * @param ChildSetting $l ChildSetting
     * @return $this The current object (for fluent API support)
     */
    public function addSettingRelatedByUpdatedBy(ChildSetting $l)
    {
        if ($this->collSettingsRelatedByUpdatedBy === null) {
            $this->initSettingsRelatedByUpdatedBy();
            $this->collSettingsRelatedByUpdatedByPartial = true;
        }

        if (!$this->collSettingsRelatedByUpdatedBy->contains($l)) {
            $this->doAddSettingRelatedByUpdatedBy($l);

            if ($this->settingsRelatedByUpdatedByScheduledForDeletion and $this->settingsRelatedByUpdatedByScheduledForDeletion->contains($l)) {
                $this->settingsRelatedByUpdatedByScheduledForDeletion->remove($this->settingsRelatedByUpdatedByScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildSetting $settingRelatedByUpdatedBy The ChildSetting object to add.
     */
    protected function doAddSettingRelatedByUpdatedBy(ChildSetting $settingRelatedByUpdatedBy): void
    {
        $this->collSettingsRelatedByUpdatedBy[]= $settingRelatedByUpdatedBy;
        $settingRelatedByUpdatedBy->setUserRelatedByUpdatedBy($this);
    }

    /**
     * @param ChildSetting $settingRelatedByUpdatedBy The ChildSetting object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeSettingRelatedByUpdatedBy(ChildSetting $settingRelatedByUpdatedBy)
    {
        if ($this->getSettingsRelatedByUpdatedBy()->contains($settingRelatedByUpdatedBy)) {
            $pos = $this->collSettingsRelatedByUpdatedBy->search($settingRelatedByUpdatedBy);
            $this->collSettingsRelatedByUpdatedBy->remove($pos);
            if (null === $this->settingsRelatedByUpdatedByScheduledForDeletion) {
                $this->settingsRelatedByUpdatedByScheduledForDeletion = clone $this->collSettingsRelatedByUpdatedBy;
                $this->settingsRelatedByUpdatedByScheduledForDeletion->clear();
            }
            $this->settingsRelatedByUpdatedByScheduledForDeletion[]= $settingRelatedByUpdatedBy;
            $settingRelatedByUpdatedBy->setUserRelatedByUpdatedBy(null);
        }

        return $this;
    }

    /**
     * Clears the current object, sets all attributes to their default values and removes
     * outgoing references as well as back-references (from other objects to this one. Results probably in a database
     * change of those foreign objects when you call `save` there).
     *
     * @return $this
     */
    public function clear()
    {
        if (null !== $this->aFileRelatedByAvatarId) {
            $this->aFileRelatedByAvatarId->removeUserRelatedByAvatarId($this);
        }
        if (null !== $this->aUserRole) {
            $this->aUserRole->removeUser($this);
        }
        $this->id = null;
        $this->email = null;
        $this->password = null;
        $this->firstname = null;
        $this->lastname = null;
        $this->state = null;
        $this->avatar_id = null;
        $this->role_id = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->applyDefaultValues();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);

        return $this;
    }

    /**
     * Resets all references and back-references to other model objects or collections of model objects.
     *
     * This method is used to reset all php object references (not the actual reference in the database).
     * Necessary for object serialisation.
     *
     * @param bool $deep Whether to also clear the references on all referrer objects.
     * @return $this
     */
    public function clearAllReferences(bool $deep = false)
    {
        if ($deep) {
            if ($this->collFilesRelatedByCreatedBy) {
                foreach ($this->collFilesRelatedByCreatedBy as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collFilesRelatedByUpdatedBy) {
                foreach ($this->collFilesRelatedByUpdatedBy as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collSettingsRelatedByCreatedBy) {
                foreach ($this->collSettingsRelatedByCreatedBy as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collSettingsRelatedByUpdatedBy) {
                foreach ($this->collSettingsRelatedByUpdatedBy as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collFilesRelatedByCreatedBy = null;
        $this->collFilesRelatedByUpdatedBy = null;
        $this->collSettingsRelatedByCreatedBy = null;
        $this->collSettingsRelatedByUpdatedBy = null;
        $this->aFileRelatedByAvatarId = null;
        $this->aUserRole = null;
        return $this;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(UserTableMap::DEFAULT_STRING_FORMAT);
    }

    // prom_model behavior

    private static $promCmsMetadata = [
      'adminMetadata' => ['icon' => "Users",],'ignoreSeeding' => false,
      /** @deprec */
      'icon' => 'Users',
      /** @deprec */
      'admin' => ['icon' => "Users",],
      'tableName' => (User::TABLE_MAP)::TABLE_NAME,
      'hasTimestamps' => false,
      'hasSoftDelete' => false,
      'columns' => ['id' => ['editable' => false,'hide' => false,'title' => "ID",'type' => "number",'required' => true,'unique' => false,'translations' => false,'autoIncrement' => true,],'email' => ['editable' => true,'hide' => false,'title' => "Email",'type' => "string",'adminMetadata' => ['ishidden' => false,'editor' => ['placement' => "main",],],'required' => true,'unique' => false,'translations' => false,'autoIncrement' => false,],'password' => ['editable' => false,'hide' => true,'title' => "Password",'type' => "password",'adminMetadata' => ['ishidden' => true,'editor' => ['placement' => "main",],],'required' => true,'unique' => false,'translations' => false,'autoIncrement' => false,],'firstname' => ['editable' => true,'hide' => false,'title' => "First name",'type' => "string",'adminMetadata' => ['editor' => ['placement' => "main",],],'required' => true,'unique' => false,'translations' => false,'autoIncrement' => false,],'lastname' => ['editable' => true,'hide' => false,'title' => "Last name",'type' => "string",'adminMetadata' => ['editor' => ['placement' => "main",],],'required' => true,'unique' => false,'translations' => false,'autoIncrement' => false,],'state' => ['editable' => true,'hide' => false,'title' => "State",'type' => "enum",'adminMetadata' => ['editor' => ['placement' => "main",],],'required' => false,'unique' => false,'translations' => false,'autoIncrement' => false,],'avatar_id' => ['editable' => true,'hide' => false,'title' => "Avatar",'type' => "file",'labelconstructor' => "{{id}}",'required' => false,'unique' => false,'translations' => false,'autoIncrement' => false,],'role_id' => ['editable' => true,'hide' => false,'title' => "Role",'type' => "relationship",'labelconstructor' => "{{label}}",'adminMetadata' => ['ishidden' => true,],'required' => false,'unique' => false,'translations' => false,'autoIncrement' => false,],],
      'hasOrdering' => false,
      'isDraftable' => false,
      'isSharable' => false,
      'ownable' => false,
    ];

    /**
     * Gets table, and it's columns, metadata
     *
     */
    public static function getPromCmsMetadata()
    {
      return static::$promCmsMetadata;
    }

    public static function isSingleton()
    {
      return str_contains((User::TABLE_MAP)::TABLE_NAME, 'singleton_');
    }
    /**
     * Code to be run before persisting the object
     * @param ConnectionInterface|null $con
     * @return bool
     */
    public function preSave(?ConnectionInterface $con = null): bool
    {
                return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface|null $con
     * @return void
     */
    public function postSave(?ConnectionInterface $con = null): void
    {
            }

    /**
     * Code to be run before inserting to database
     * @param ConnectionInterface|null $con
     * @return bool
     */
    public function preInsert(?ConnectionInterface $con = null): bool
    {
                return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface|null $con
     * @return void
     */
    public function postInsert(?ConnectionInterface $con = null): void
    {
            }

    /**
     * Code to be run before updating the object in database
     * @param ConnectionInterface|null $con
     * @return bool
     */
    public function preUpdate(?ConnectionInterface $con = null): bool
    {
                return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface|null $con
     * @return void
     */
    public function postUpdate(?ConnectionInterface $con = null): void
    {
            }

    /**
     * Code to be run before deleting the object in database
     * @param ConnectionInterface|null $con
     * @return bool
     */
    public function preDelete(?ConnectionInterface $con = null): bool
    {
                return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface|null $con
     * @return void
     */
    public function postDelete(?ConnectionInterface $con = null): void
    {
            }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);
            $inputData = $params[0];
            $keyType = $params[1] ?? TableMap::TYPE_PHPNAME;

            return $this->importFrom($format, $inputData, $keyType);
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = $params[0] ?? true;
            $keyType = $params[1] ?? TableMap::TYPE_PHPNAME;

            return $this->exportTo($format, $includeLazyLoadColumns, $keyType);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
