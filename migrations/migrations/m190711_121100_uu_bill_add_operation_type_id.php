<?php

use app\models\OperationType;

/**
 * Handles the creation of table `operation_type`.
 */
class m190711_121100_uu_bill_add_operation_type_id extends \app\classes\Migration
{
    protected static $typeField = 'operation_type_id';

    protected static $uniqueKeyColumnsOld = [
        'date',
        'client_account_id',
    ];
    protected static $uniqueKeyColumns = [
        'date',
        'client_account_id',
        'operation_type_id',
    ];

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
        $this->update(
            OperationType::tableName(),
            [
                'name' => 'Коррекционный',
            ],
            ['id' => OperationType::ID_CORRECTION]
        );

        // alter related tables
        $defaultId = OperationType::getDefaultId();
        $relatedTable = \app\modules\uu\models\Bill::tableName();
        $this->addColumn(
            $relatedTable,
            self::$typeField,
            $this
                ->integer(11)
                ->notNull()
                ->defaultValue($defaultId)
                ->after('id')
        );

        $this->addForeignKey(
            'fk-' . $relatedTable . '-' . self::$typeField,
            $relatedTable, self::$typeField,
            self::getTypeTableName(), 'id'
        );

        // create new unique index
        $this->createIndex(
            'uniq-' . $relatedTable . '-' . implode('-', self::$uniqueKeyColumns),
            $relatedTable,
            self::$uniqueKeyColumns,
            true
        );

        // drop old unique index
        $this->dropIndex(
            'uniq-uu_bill-date-client_account_id',
            $relatedTable
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // alter related tables
        $relatedTable = \app\modules\uu\models\Bill::tableName();
        $this->dropForeignKey(
            'fk-' . $relatedTable . '-' . self::$typeField,
            $relatedTable
        );

        $this->dropIndex(
            'idx-' . $relatedTable . '-' . self::$typeField,
            $relatedTable
        );

        // restore old unique index
        $this->createIndex(
            'uniq-' . $relatedTable . '-' . implode('-', self::$uniqueKeyColumnsOld),
            $relatedTable,
            self::$uniqueKeyColumnsOld,
            true
        );

        // drop unique index
        $this->dropIndex(
            'uniq-' . $relatedTable . '-' . implode('-', self::$uniqueKeyColumns),
            $relatedTable
        );

        $this->dropColumn($relatedTable, self::$typeField);
    }
}
