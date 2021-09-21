<?php

namespace app\classes\monitoring;

use app\classes\helpers\DependecyHelper;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\ClientContract;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use app\classes\Html;

class ActiveAccountsWithoutActiveServices extends Component implements MonitoringInterface
{
    const CACHE_KEY = 'ActiveAccountsWithoutActiveServices';

    /**
     * @return string
     */
    public function getKey()
    {
        return 'ActiveAccountsWithoutActiveServices';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Включенные ЛС без активных услуг';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Включенные ЛС без активных услуг';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            [
                'label' => 'Статус договора',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a(
                            "<div class='glyphicon glyphicon-edit'></div>",
                            ['/dictionary/business-process-status/edit', 'id' => $data['business_process_status_id']],
                            ['target' => '_blank']
                        ) . ' ' .
                        (
                        $data['status_color'] ?
                            '<b style="background:' . $data['status_color'] . ';">' . $data['status_name'] . '</b>' :
                            '<b>' . $data['status_name'] . '</b>'
                        );
                }
            ],
            [
                'label' => 'Процесс',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['business_name'];
                }
            ],
            [
                'label' => 'Клиент',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['contragent_name'];
                }
            ],
            [
                'label' => 'ЛС',
                'format' => 'raw',
                'value' => function ($data) {
                    return implode(', ', array_map(function ($accountId) {
                        return Html::a(
                            $accountId,
                            ['/client/view', 'id' => $accountId],
                            ['target' => '_blank']
                        );
                    }, explode(',', $data['account_ids'])));
                }
            ],
        ];
    }

    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
//        if (
//            !\Yii::$app->request->get('page')
//            || !\Yii::$app->cache->exists(self::CACHE_KEY)
//        ) {
        $data = $this->getDataFromDb($ex);
//            \Yii::$app->cache->set(self::CACHE_KEY, $data, DependecyHelper::TIMELIFE_HOUR);
//        } else {
//            $data = \Yii::$app->cache->get(self::CACHE_KEY);
//        }
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
                  ) a),

     cl as (
         select c.contract_id, 
                sum(if(cls.client is null, 0, 1)) as count_active_account, 
                group_concat(c.id order by c.id) account_ids 
         from clients c
            inner join client_contract cc on c.contract_id = cc.id
            left join cls on (cls.client = c.client)
         where cc.business_process_status_id not in ({$offIds})
         group by c.contract_id
         having count_active_account = 0
     )

select bps.name status_name,
       bps.color status_color,
       cg.name as contragent_name,
       cc.business_process_status_id,
       b.name business_name,
       cl.account_ids,
       cl.contract_id
from cl
              inner join client_contract cc on cc.id = cl.contract_id
              inner join client_contragent cg on cc.contragent_id = cg.id
              inner join client_contract_business b on cc.business_id = b.id
              inner join client_contract_business_process_status bps on cc.business_process_status_id = bps.id
order by cl.contract_id

# aaa
SQL;
        return ClientAccount::getDb()->createCommand($sql, [
            ':date' => $date,
        ])
            ->queryAll();
    }

}