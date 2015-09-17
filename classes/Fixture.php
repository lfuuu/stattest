<?php
namespace app\classes;

use Yii;
use yii\console\Exception;
use yii\db\TableSchema;


class Fixture extends \yii\test\Fixture
{
    /**
     * @var Connection
     */
    public $db;

    public $tableName;

    /**
     * @var string|boolean the file path or path alias of the data file that contains the fixture data
     * to be returned by [[getData()]]. If this is not set, it will default to `FixturePath/data/TableName.php`,
     * where `FixturePath` stands for the directory containing this fixture class, and `TableName` stands for the
     * name of the table associated with this fixture. You can set this property to be false to prevent loading any data.
     */
    public $dataFile;

    /**
     * @var array the data rows. Each array element represents one row of data (column name => column value).
     */
    public $data = [];

    /**
     * @var TableSchema the table schema for the table associated with this fixture
     */
    private $_table;


    public function init()
    {
        parent::init();
        $this->db = Yii::$app->getDb();
    }

    /**
     * Loads the fixture.
     *
     * It will then populate the table with the data returned by [[getData()]].
     *
     * If you override this method, you should consider calling the parent implementation
     * so that the data returned by [[getData()]] can be populated into the table.
     */
    public function load()
    {
        parent::load();

        $table = $this->getTableSchema();

        foreach ($this->getData() as $alias => $row) {
            $this->db->createCommand()->insert($table->fullName, $row)->execute();
            if ($table->sequenceName !== null) {
                foreach ($table->primaryKey as $pk) {
                    if (!isset($row[$pk])) {
                        $row[$pk] = $this->db->getLastInsertID($table->sequenceName);
                        break;
                    }
                }
            }
            $this->data[$alias] = $row;
        }
    }

    /**
     * Unloads the fixture.
     *
     * The default implementation will clean up the table by calling [[resetTable()]].
     */
    public function unload()
    {
        $this->resetTable();
        parent::unload();
    }

    /**
     * Returns the fixture data.
     *
     * The default implementation will try to return the fixture data by including the external file specified by [[dataFile]].
     * The file should return an array of data rows (column name => column value), each corresponding to a row in the table.
     *
     * If the data file does not exist, an empty array will be returned.
     *
     * @return array the data rows to be inserted into the database table.
     */
    protected function getData()
    {
        if ($this->dataFile === null) {
            $class = new \ReflectionClass($this);
            $dataFile = dirname($class->getFileName()) . '/data/' . $this->getTableSchema()->fullName . '.php';

            return is_file($dataFile) ? require($dataFile) : [];
        }

        $dataFile = Yii::getAlias($this->dataFile);
        if (is_file($dataFile)) {
            return require($dataFile);
        } else {
            throw new Exception("Fixture data file does not exist: {$this->dataFile}");
        }
    }

    /**
     * Removes all existing data from the specified table and resets sequence number to 1 (if any).
     * This method is called before populating fixture data into the table associated with this fixture.
     */
    protected function resetTable()
    {
        $table = $this->getTableSchema();
        $this->db->createCommand()->delete($table->fullName)->execute();
        if ($table->sequenceName !== null) {
            $this->db->createCommand()->resetSequence($table->fullName, 1)->execute();
        }
    }

    /**
     * @return TableSchema the schema information of the database table associated with this fixture.
     * @throws Exception if the table does not exist
     */
    public function getTableSchema()
    {
        if ($this->_table !== null) {
            return $this->_table;
        }

        $this->_table = $this->db->getSchema()->getTableSchema($this->tableName);
        if ($this->_table === null) {
            throw new Exception("Table does not exist: {$this->tableName}");
        }

        return $this->_table;
    }

}
