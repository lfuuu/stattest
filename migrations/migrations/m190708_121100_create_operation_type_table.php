<?php

use app\models\Bill;
use app\models\OperationType;

/**
 * Handles the creation of table `operation_type`.
 */
class m190708_121100_create_operation_type_table extends \app\classes\Migration
{
    protected static $typeField = 'operation_type_id';

    /**
     * @return string
     */
    protected static function getTypeTableName()
    {
        return OperationType::tableName();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // create type table
        $this->createTable(self::getTypeTableName(), [
            'id' => $this->primaryKey(),
            'key' => $this->string(32)->notNull(),
            'name' => $this->string(255)->notNull(),
            'is_convertible' => $this->boolean()->notNull()->defaultValue(false),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addCommentOnTable(self::getTypeTableName(), 'Тип расчётной операции / документа');

        // insert types
        $typePriceId = OperationType::ID_PRICE;
        $typeCostId = OperationType::ID_COST;
        $typeCorrectionId = OperationType::ID_CORRECTION;

        $this->insert(self::getTypeTableName(), [
            'id' => $typePriceId,
            'key' => 'price',
            'name' => 'Доходный',
            'is_convertible' => true,
        ]);

        $this->insert(self::getTypeTableName(), [
            'id' => $typeCostId,
            'key' => 'cost',
            'name' => 'Расходный',
            'is_convertible' => false,
        ]);

        $this->insert(self::getTypeTableName(), [
            'id' => $typeCorrectionId,
            'key' => 'correction',
            'name' => 'Коррекционны',
            'is_convertible' => false,
        ]);

        // alter related tables
        $relatedTable = Bill::tableName();
        $this->addColumn(
            $relatedTable,
            self::$typeField,
            $this
                ->integer(11)
                ->notNull()
                ->defaultValue($typePriceId)
                ->after('id')
        );

        $this->createIndex(
            'idx-' . $relatedTable . '-' . self::$typeField,
            $relatedTable,
            self::$typeField
        );

        $this->addForeignKey(
            'fk-' . $relatedTable . '-' . self::$typeField,
            $relatedTable, self::$typeField,
            self::getTypeTableName(), 'id'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // alter related tables
        $relatedTable = Bill::tableName();
        $this->dropForeignKey(
            'fk-' . $relatedTable . '-' . self::$typeField,
            $relatedTable
        );

        $this->dropIndex(
            'idx-' . $relatedTable . '-' . self::$typeField,
            $relatedTable
        );

        $this->dropColumn($relatedTable, self::$typeField);

        // drop type table
        $this->dropTable(self::getTypeTableName());
    }
}
