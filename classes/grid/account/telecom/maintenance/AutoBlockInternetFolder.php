<?php

namespace app\classes\grid\account\telecom\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\billing\Locks;
use app\models\ClientBlockedComment;
use app\models\BusinessProcessStatus;
use app\models\Number;
use app\models\UsageIpPorts;
use app\models\UsageVoip;
use app\modules\nnp\models\NdcType;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\db\Expression;
use yii\db\Query;

class AutoBlockInternetFolder extends AccountGridFolder
{
    public $block_date;

    public $_isGenericFolder = false;

    /**
     * @return string
     */
    public function getName()
    {
        return 'Интернет-Блок';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            'status',
            'id',
            'company',
            'currency',
            'block_date',
            'manager',
            'region',
            'legal_entity',
            'comment',
        ];
    }

    /**
     * @param Query $query
     */
    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $uuSubQuery = (new Query())
            ->select('client_account_id')
            ->from(['uu' => AccountTariff::tableName()])
            ->where([
                'uu.service_type_id' => ServiceType::ID_INTERNET,
            ])
            ->andWhere(['IS NOT', 'uu.tariff_period_id', null]);

        $query->addSelect(['block_date' => $this->getBlockDateQuery(), 'cbc.comment'])
            ->leftJoin(['uv' => UsageIpPorts::tableName()], 'uv.client = c.client AND CAST(NOW() AS DATE) BETWEEN uv.actual_from AND uv.actual_to')
            ->leftJoin(['uu' => $uuSubQuery], 'uu.client_account_id = c.id')
            ->leftJoin(['cbc' => ClientBlockedComment::tableName()], 'cbc.account_id = c.id')
            ->andWhere([
                'cr.business_id' => $this->grid->getBusiness(),
                'cr.business_process_status_id' => $this->getBusinessProcessStatus(),
                'c.is_blocked' => 0
            ])
            ->andWhere(['OR',
                ['IS NOT', 'uu.client_account_id', null],
                ['IS NOT', 'uv.client', null]
            ]);

        try {
            Locks::setPgTimeout(Locks::PG_ACCOUNT_TIMEOUT);
            $clientsIDs = Locks::getVoipLocks();
        } catch (\Exception $e) {
            $clientsIDs = [];
        }

        if (count($clientsIDs)) {
            $query->andWhere(['IN', 'c.id', $clientsIDs]);
        } else {
            $query->andWhere(new Expression('false'));
        }
    }

    /**
     * Получение статуса бизнес процесса
     *
     * @return int
     */
    protected function getBusinessProcessStatus()
    {
        return BusinessProcessStatus::TELEKOM_MAINTENANCE_WORK;
    }
}