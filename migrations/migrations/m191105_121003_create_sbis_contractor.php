<?php

use app\modules\sbisTenzor\models\SBISContractor;

/**
 * Class m191105_121003_create_sbis_contractor
 */
class m191105_121003_create_sbis_contractor extends \app\classes\Migration
{
    public $tableName;
    public $tableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8';

    protected static $index1Fields = ['tin', 'iec'];
    protected static $index2Fields = ['itn', 'is_private'];

    protected static $uniqueKeyColumns = [
        'tin',
        'itn',
        'iec',
        'inila',
    ];
    protected static $uniqueKeySuffix = 'tin-itn-iec-inila';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->tableName = SBISContractor::tableName();

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),

            'tin' => $this->string(10),
            'itn' => $this->string(12),
            'iec' => $this->string(36),

            'full_name' => $this->string(255)->notNull(),
            'email' => $this->string(255),
            'phone' => $this->string(255),

            'exchange_id' => $this->string(46),
            'exchange_id_is' => $this->string(255),
            'exchange_id_spp' => $this->string(255),
            'country_code' => $this->smallInteger()->unsigned(),

            'is_private' => $this->boolean()->notNull(),
            'inila' => $this->string(15),
            'first_name' => $this->string(60),
            'last_name' => $this->string(60),
            'middle_name' => $this->string(60),

            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime(),

        ], $this->tableOptions);

        $this->addCommentOnTable($this->tableName, 'Данные по контрагенту в системе СБИС');

        // indexes
        $this->createIndex(
            'idx-' . $this->tableName . '-' . implode('-', self::$index1Fields),
            $this->tableName,
            self::$index1Fields
        );
        $this->createIndex(
            'idx-' . $this->tableName . '-' . implode('-', self::$index2Fields),
            $this->tableName,
            self::$index2Fields
        );

        // create new unique index
        $this->createIndex(
            'uniq-' . $this->tableName . '-' . self::$uniqueKeySuffix,
            $this->tableName,
            self::$uniqueKeyColumns,
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->tableName = SBISContractor::tableName();

        // foreign keys

        // indexes
        $this->dropIndex(
            'idx-' . $this->tableName . '-' . implode('-', self::$index2Fields),
            $this->tableName
        );
        $this->dropIndex(
            'idx-' . $this->tableName . '-' . implode('-', self::$index1Fields),
            $this->tableName
        );

        $this->dropIndex(
            'uniq-' . $this->tableName . '-' . self::$uniqueKeySuffix,
            $this->tableName
        );

        $this->dropTable($this->tableName);
    }
}
