<?php

use app\modules\sbisTenzor\models\SBISExchangeGroup;

/**
 * Class m191105_120401_create_sbis_exchange_group
 */
class m191105_120401_create_sbis_exchange_group extends \app\classes\Migration
{
    public $tableName;
    public $tableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->tableName = SBISExchangeGroup::tableName();

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),

        ], $this->tableOptions);

        $this->addCommentOnTable($this->tableName, 'Группа первичных документов для обмена в системе СБИС');

        // insert data
        $this->insert($this->tableName, [
            'id' => SBISExchangeGroup::ACT,
            'name' => 'Акт',
        ]);
        $this->insert($this->tableName, [
            'id' => SBISExchangeGroup::ACT_AND_INVOICE_2016,
            'name' => 'Акт + Счет-фактура(2016)',
        ]);
        $this->insert($this->tableName, [
            'id' => SBISExchangeGroup::ACT_AND_INVOICE_2019,
            'name' => 'Акт + Счет-фактура(2019)',
        ]);

        // indexes
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->tableName = SBISExchangeGroup::tableName();

        // foreign keys

        // indexes

        $this->dropTable($this->tableName);
    }
}
