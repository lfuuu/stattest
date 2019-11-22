<?php

use app\models\BillDocument;
use app\modules\sbisTenzor\models\SBISExchangeForm;

/**
 * Class m191105_120100_create_sbis_exchange_form
 */
class m191105_120100_create_sbis_exchange_form extends \app\classes\Migration
{
    public $tableName;
    public $tableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->tableName = SBISExchangeForm::tableName();

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'type' => $this->string(15)->notNull(),
            'name' => $this->string(255)->notNull(),
            'full_name' => $this->string(255)->notNull(),

            'version' => $this->string(5)->notNull(),
            'knd_code' => $this->integer()->notNull(),
            'file_pattern' => $this->string(255)->notNull(),

            'starts_at' => $this->dateTime(),
            'expires_at' => $this->dateTime(),

        ], $this->tableOptions);

        $this->addCommentOnTable($this->tableName, 'Форма первичного документа (файла) в системе документооборота');

        // insert data
        $this->insert($this->tableName, [
            'id' => SBISExchangeForm::ACT_2016_5_02,
            'type' => BillDocument::TYPE_ACT,
            'name' => 'Акт выполненных работ (с 2016 г.)',
            'full_name' => 'Акт выполненных работ (с 2016 г.), ФНС 5.02 (с 01.01.2016)',
            'version' => '5.02',
            'knd_code' => 1175012,
            'file_pattern' => 'DP_REZRUISP_{A}_{O}_{GGGGMMDD}_{N}',
            'starts_at' => '2016-01-01 00:00:00',
        ]);
        $this->insert($this->tableName, [
            'id' => SBISExchangeForm::INVOICE_2016_5_02,
            'type' => BillDocument::TYPE_INVOICE,
            'name' => 'Счет-фактура (с 2016 г.)',
            'full_name' => 'Счет-фактура (с 2016 г.), ФНС 5.02 (до 31.12.2019)',
            'version' => '5.02',
            'knd_code' => 1115125,
            'file_pattern' => 'ON_SCHFDOPPR_{A}_{O}_{GGGGMMDD}_{N}',
            'expires_at' => '2019-12-31 23:59:59',
        ]);
        $this->insert($this->tableName, [
            'id' => SBISExchangeForm::INVOICE_2019_5_01,
            'type' => BillDocument::TYPE_INVOICE,
            'name' => 'Счет-фактура (с 2019 г.)',
            'full_name' => 'Счет-фактура (с 2019 г.), ФНС 5.01 (с 02.02.2019)',
            'version' => '5.01',
            'knd_code' => 1115131,
            'file_pattern' => 'ON_NSCHFDOPPR_{A}_{O}_{GGGGMMDD}_{N}',
            'starts_at' => '2019-02-02 00:00:00',
        ]);

        // indexes
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->tableName = SBISExchangeForm::tableName();

        // foreign keys

        // indexes

        $this->dropTable($this->tableName);
    }
}
