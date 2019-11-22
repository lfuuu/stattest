<?php

use app\modules\sbisTenzor\models\SBISExchangeGroupForm;
use app\modules\sbisTenzor\models\SBISExchangeGroup;
use app\modules\sbisTenzor\models\SBISExchangeForm;

/**
 * Class m191105_120702_create_sbis_exchange_group_form
 */
class m191105_120702_create_sbis_exchange_group_form extends \app\classes\Migration
{
    public $tableName;
    public $tableNameGroup;
    public $tableNameForm;
    public $tableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->tableName = SBISExchangeGroupForm::tableName();
        $this->tableNameGroup = SBISExchangeGroup::tableName();
        $this->tableNameForm = SBISExchangeForm::tableName();

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'group_id' => $this->integer(11)->notNull(),
            'form_id' => $this->integer(11)->notNull(),

        ], $this->tableOptions);

        $this->addCommentOnTable($this->tableName, 'Таблица связи групп и форм первичных документов для обмена в системе СБИС');

        // foreign keys
        $this->addForeignKey(
            'fk-' . $this->tableName . '-' . 'group_id',
            $this->tableName, 'group_id',
            $this->tableNameGroup, 'id'
        );
        $this->addForeignKey(
            'fk-' . $this->tableName . '-' . 'form_id',
            $this->tableName, 'form_id',
            $this->tableNameForm, 'id'
        );

        // insert data
        $this->insert($this->tableName, [
            'group_id' => SBISExchangeGroup::ACT,
            'form_id' => SBISExchangeForm::ACT_2016_5_02,
        ]);

        $this->insert($this->tableName, [
            'group_id' => SBISExchangeGroup::ACT_AND_INVOICE_2016,
            'form_id' => SBISExchangeForm::ACT_2016_5_02,
        ]);
        $this->insert($this->tableName, [
            'group_id' => SBISExchangeGroup::ACT_AND_INVOICE_2016,
            'form_id' => SBISExchangeForm::INVOICE_2016_5_02,
        ]);

        $this->insert($this->tableName, [
            'group_id' => SBISExchangeGroup::ACT_AND_INVOICE_2019,
            'form_id' => SBISExchangeForm::ACT_2016_5_02,
        ]);
        $this->insert($this->tableName, [
            'group_id' => SBISExchangeGroup::ACT_AND_INVOICE_2019,
            'form_id' => SBISExchangeForm::INVOICE_2019_5_01,
        ]);

        // indexes
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->tableName = SBISExchangeGroupForm::tableName();

        // indexes

        // foreign keys
        $this->dropForeignKey(
            'fk-' . $this->tableName . '-' . 'form_id',
            $this->tableName
        );
        $this->dropForeignKey(
            'fk-' . $this->tableName . '-' . 'group_id',
            $this->tableName
        );

        $this->dropTable($this->tableName);
    }
}
