<?php

use app\models\ClientAccount;
use app\modules\sbisTenzor\classes\SBISDocumentStatus;
use app\modules\sbisTenzor\classes\SBISExchangeStatus;
use app\modules\sbisTenzor\models\SBISDocument;
use app\modules\sbisTenzor\models\SBISExchangeGroup;

/**
 * Class m200225_121917_client_add_exchange_status
 */
class m200225_121917_client_add_exchange_status extends \app\classes\Migration
{
    protected static $column = 'exchange_status';
    protected static $columnExchangeGroupId = 'exchange_group_id';

    public $tableName;

    /**
     * Up
     */
    public function safeUp()
    {
        $this->tableName = ClientAccount::tableName();

        // change all invoices 2016 -> invoices 2019
        $this->update(
            $this->tableName,
            [self::$columnExchangeGroupId => SBISExchangeGroup::ACT_AND_INVOICE_2019,],
            [self::$columnExchangeGroupId => SBISExchangeGroup::ACT_AND_INVOICE_2016,]
        );

        $this->addColumn(
            $this->tableName,
            self::$column,
            $this
                ->smallInteger()
                ->notNull()
                ->defaultValue(SBISExchangeStatus::UNKNOWN)
                ->after(self::$columnExchangeGroupId)
        );

        // всем подключённым клиентам, у которых есть подтвежденные пакеты
        // ставим статус интеграции со СБИС "Настроен"
        $clientIds =
            SBISDocument::find()
                ->select('client_account_id')
                ->andWhere(['state' => SBISDocumentStatus::ACCEPTED])
                ->joinWith('clientAccount as c1', false)
                ->andWhere(['c1.' . self::$column => SBISExchangeStatus::UNKNOWN])
                ->andWhere(['IS NOT', 'c1.' . self::$columnExchangeGroupId, null])
                ->groupBy(['client_account_id'])
                ->column();
        $this->update(
            $this->tableName,
            [self::$column => SBISExchangeStatus::SET_UP,],
            ['id' => $clientIds,]
        );

        // создаем индекс для поиска по проверенным
        $this->createIndex(
            'idx-' . $this->tableName . '-' . self::$column,
            $this->tableName,
            self::$column
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->tableName = ClientAccount::tableName();

        $this->dropIndex(
            'idx-' . $this->tableName . '-' . self::$column,
            $this->tableName
        );

        $this->dropColumn($this->tableName, self::$column);
    }
}
