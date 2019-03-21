<?php

/**
 * Handles the creation of table `payment_template`.
 */
class m190315_121260_create_payment_template_table extends \app\classes\Migration
{
    public $tableName = 'payment_template';
    public $tableOptions;

    public $tableNameType = 'payment_template_type';
    public $tableNameCountry = 'country';
    public $tableNameUser = 'user_users';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'type_id' => $this->integer(11)->notNull(),
            'country_code' => $this->integer(4)->notNull(),
            'version' => $this->bigInteger()->notNull(),
            'is_active' => $this->boolean()->notNull(),
            'is_default' => $this->boolean()->notNull(),
            'content' => $this->text()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->null(),
            'updated_by' => $this->integer(11)->null(),

        ], $this->tableOptions);

        $this->addCommentOnTable($this->tableName, 'Шаблоны для документов');

        // indexes
        $this->createIndex(
            'idx-' . $this->tableName . '-'  . 'type_id',
            $this->tableName,
            'type_id'
        );
        $this->createIndex(
            'idx-' . $this->tableName . '-'  . 'country_code',
            $this->tableName,
            'country_code'
        );
        $this->createIndex(
            'idx-' . $this->tableName . '-'  . 'updated_by',
            $this->tableName,
            'updated_by'
        );
        $this->createIndex(
            'uidx-' . $this->tableName . '-'  . 'type_id' . '-'  . 'country_code' . '-'  . 'version',
            $this->tableName,
            [
                'type_id',
                'country_code',
                'version',
            ],
            true
        );

        // foreign keys
        $this->addForeignKey(
            'fk-' . $this->tableName . '-' . 'type_id',
            $this->tableName, 'type_id',
            $this->tableNameType, 'id'
        );
        $this->addForeignKey(
            'fk-' . $this->tableName . '-' . 'country_code',
            $this->tableName, 'country_code',
            $this->tableNameCountry, 'code'
        );
        $this->addForeignKey(
            'fk-' . $this->tableName . '-' . 'updated_by',
            $this->tableName, 'updated_by',
            $this->tableNameUser, 'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // foreign keys
        $this->dropForeignKey(
            'fk-' . $this->tableName . '-' . 'updated_by',
            $this->tableName
        );
        $this->dropForeignKey(
            'fk-' . $this->tableName . '-' . 'country_code',
            $this->tableName
        );
        $this->dropForeignKey(
            'fk-' . $this->tableName . '-' . 'type_id',
            $this->tableName
        );

        // indexes
        $this->dropIndex(
            'uidx-' . $this->tableName . '-'  . 'type_id' . '-'  . 'country_code' . '-'  . 'version',
            $this->tableName
        );

        $this->dropIndex(
            'idx-' . $this->tableName . '-'  . 'updated_by',
            $this->tableName
        );
        $this->dropIndex(
            'idx-' . $this->tableName . '-'  . 'country_code',
            $this->tableName
        );
        $this->dropIndex(
            'idx-' . $this->tableName . '-'  . 'type_id',
            $this->tableName
        );

        $this->dropTable($this->tableName);
    }
}
