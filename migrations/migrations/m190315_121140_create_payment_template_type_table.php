<?php

/**
 * Handles the creation of table `payment_template_type`.
 */
class m190315_121140_create_payment_template_type_table extends \app\classes\Migration
{
    public $tableName = 'payment_template_type';
    public $tableOptions;

    public $tableNameUser = 'user_users';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'note' => $this->string(255)->null(),
            'is_enabled' => $this->boolean()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->null(),
            'updated_by' => $this->integer(11)->null(),

        ], $this->tableOptions);

        $this->addCommentOnTable($this->tableName, 'Тип расчетных документов');

        // indexes
        $this->createIndex(
            'idx-' . $this->tableName . '-'  . 'updated_by',
            $this->tableName,
            'updated_by'
        );

        // foreign keys
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

        // indexes
        $this->dropIndex(
            'idx-' . $this->tableName . '-'  . 'updated_by',
            $this->tableName
        );

        $this->dropTable($this->tableName);
    }
}
