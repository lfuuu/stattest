<?php

use app\models\OperationType;

/**
 * Handles the creation of table `operation_type`.
 */
class m190711_170000_acc_entry_add_operation_type_id extends \app\classes\Migration
{
    protected static $typeField = 'operation_type_id';

    protected static $uniqueKeyColumnsOld = [
        'date',
        'type_id',
        'account_tariff_id',
        'tariff_period_id',
        'is_next_month',
    ];
    protected static $uniqueKeyColumns = [
        'date',
        'type_id',
        'account_tariff_id',
        'tariff_period_id',
        'is_next_month',
        'operation_type_id',
    ];
    protected static $uniqueKeyOld = 'uniq-date-type_id-account_tariff_id-tariff_period_id';
    protected static $uniqueKeySuffix = 'date-type-at-tp-next-oper';

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
        // alter related tables
        $defaultId = OperationType::getDefaultId();
        $relatedTable = \app\modules\uu\models\AccountEntry::tableName();
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
            'uniq-' . $relatedTable . '-' . self::$uniqueKeySuffix,
            $relatedTable,
            self::$uniqueKeyColumns,
            true
        );

        // drop old unique index
        $this->dropIndex(
            self::$uniqueKeyOld,
            $relatedTable
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // alter related tables
        $relatedTable = \app\modules\uu\models\AccountEntry::tableName();
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
            self::$uniqueKeyOld,
            $relatedTable,
            self::$uniqueKeyColumnsOld,
            true
        );

        // drop unique index
        $this->dropIndex(
            'uniq-' . $relatedTable . '-' . self::$uniqueKeySuffix,
            $relatedTable
        );

        $this->dropColumn($relatedTable, self::$typeField);
    }
}