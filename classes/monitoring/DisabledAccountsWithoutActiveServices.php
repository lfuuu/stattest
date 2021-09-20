<?php

namespace app\classes\monitoring;

use app\classes\helpers\DependecyHelper;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\modules\uu\models\AccountTariff;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use app\classes\Html;
use yii\db\Expression;

class DisabledAccountsWithoutActiveServices extends Component implements MonitoringInterface
{
    const CACHE_KEY = 'DisabledAccountsWithoutActiveServices';

    /**
     * @return string
     */
    public function getKey()
    {
        return 'DisabledAccountsWithoutActiveServices';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Выключенные ЛС без активных услуг';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Выключенные ЛС без активных услуг';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            [
                'label' => 'ЛС',
                'format' => 'raw',
                'value' => function ($data) {
                    return
                        Html::a(
                            $data['client_account_id'],
                            ['/client/view', 'id' => $data['client_account_id']],
                            ['target' => '_blank']
                        );
                }
            ],
            [
                'label' => 'Статус договора',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['status_color'] ?
                        '<b style="background:' . $data['status_color'] . ';">' . $data['status_name'] . '</b>' :
                        '<b>' . $data['status_name'] . '</b>';
                }
            ],
            [
                'label' => 'Клиент',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['contragent_name'];
                }
            ],            [
                'label' => 'БПС id',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['business_process_status_id'];
                }
            ],
        ];
    }

    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
        $ex = array_filter(array_map(
            function ($v) {
                return preg_replace('/[^0-9]/', '', $v);
            }, explode(',', \Yii::$app->request->get('ex', ''))));

        if (
            !\Yii::$app->request->get('page')
            || !\Yii::$app->cache->exists(self::CACHE_KEY)
        ) {
            $data = $this->getDataFromDb($ex);
            \Yii::$app->cache->set(self::CACHE_KEY, $data, DependecyHelper::TIMELIFE_HOUR);
        } else {
            $data=\Yii::$app->cache->get(self::CACHE_KEY);
        }
        return new ArrayDataProvider([
            'allModels' => $data,
        ]);
    }

    private function getDataFromDb($ex)
    {
        $date = (new \DateTime('now'))->format(DateTimeZoneHelper::DATE_FORMAT);
        $offIds = implode(', ', array_merge(
            ClientContract::getOffBpsIds(),
            ClientContract::$neutralBPSids,
            [8, 142], //Подключаемые,
            $ex
        ));

        $sql = <<<SQL
with cls as (select distinct a.client
             from (
                      select u.client
                      from usage_extra u
                      where u.actual_to >= :date

                      union all

                      select u.client
                      from usage_welltime u
                      where u.actual_to >= :date

                      union all

                      select u.client
                      from usage_ip_ports u
                      where u.actual_to >= :date

                      union all

                      select u.client
                      from usage_sms u
                      where u.actual_to >= :date

                      union all

                      select u.client
                      from usage_virtpbx u
                      where u.actual_to >= :date

                      union all

                      select u.client
                      from usage_voip u
                      where u.actual_to >= :date

                      union all

                      select c.client
                      from usage_trunk u
                               inner join clients c on u.client_account_id = c.id
                      where u.actual_to >= :date

                      union all

                      select c.client
                      from uu_account_tariff u
                               inner join clients c on u.client_account_id = c.id
                      where tariff_period_id is not null
                        and prev_account_tariff_id is null
                  ) a)

select c.id as client_account_id, bps.name status_name, bps.color status_color, cg.name as contragent_name, cc.business_process_status_id 
from clients c, client_contract cc, client_contragent cg, client_contract_business_process_status bps
where contract_id=cc.id
  and cc.contragent_id = cg.id
  and cc.business_process_status_id = bps.id
  and cc.business_process_status_id not in ({$offIds})
  and c.client not in (select cls.client from cls)
ORDER BY c.id
# aaa
SQL;
        return ClientAccount::getDb()->createCommand($sql, [
            ':date' => $date,
            ])
            ->queryAll();
    }

}