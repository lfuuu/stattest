<?php

use app\models\ClientAccount;

class StatIp
{
    private $account = null;

    public function __construct($fixclient)
    {
        $this->account = ClientAccount::findOne(['id' => $fixclient]);

        \app\classes\Assert::isObject($this->account);

        global $design;

        $design->assign('detality', $detality = get_param_protected('detality', 'ip'));

        $dateFrom = new DatePickerValues('date_from', 'first');
        $dateTo = new DatePickerValues('date_to', 'last');

        DatePickerPeriods::assignStartEndMonth($dateFrom->day, 'prev_', '-1 month');

        $stats = [];

        if (get_param_raw('do', '') !== '') {
            $stats = $this->exec($detality, $this->account->id, $dateFrom->getSqlDay(), $dateTo->getSqlDay());
        }

        $design->assign('stats', $stats);
        $design->assign('account', $this->account);
        $design->AddMain('stats/ip.tpl');
    }

    private function exec($detality, $accountId, $dateFrom, $dateTo)
    {
        return \Yii::$app
            ->dbPgCallLegs
            ->createCommand($detality == 'call' ? $this->getSqlCalls($accountId, $dateFrom, $dateTo) : $this->getSqlIpCount($accountId, $dateFrom, $dateTo))
            ->queryAll();
    }

    private function getSqlIpCount($accountId, $dateFrom, $dateTo)
    {
        return 'SELECT
  ip,
  count(*) AS cnt
FROM (' .$this->getSqlCalls($accountId, $dateFrom, $dateTo). ') a
WHERE ip IS NOT NULL
GROUP BY ip
ORDER BY ip';
    }

    private function getSqlCalls($accountId, $dateFrom, $dateTo)
    {
        $sql = <<<SQL

select 
cl1.number::text number_a, 
cl1.did::text number_b, 
cl2.sip_ip ip, 
to_char(cl1.start_time, 'YYYY-MM-DD HH24:MI:SS') date,
true as orig
from vpbx.call_leg cl1 
left join vpbx.call_leg cl2 on (cl1.last_linked_id = cl2.uniqueid) 
where 
cl1.account_id = '{$accountId}' 
and cl1.object_type = 'pstn' 
and cl1.orig = 't' 
and cl1.start_time >= '{$dateFrom}' 
and cl1.start_time < '{$dateTo}'
union 
select 
cl1.did::text number_a, 
cl1.number::text number_b, 
cl2.sip_ip as ip, 
to_char(cl1.start_time, 'YYYY-MM-DD HH24:MI:SS') date,
false as orig
from vpbx.call_leg cl1 
left join vpbx.call_leg cl2 on (cl1.last_linked_id = cl2.uniqueid) 
where 
cl1.account_id = '{$accountId}' 
and cl1.object_type = 'pstn' 
and cl1.orig = 'f' 
and cl1.start_time >= '{$dateFrom}' 
and cl1.start_time < '{$dateTo}'

order by date
limit 10000
SQL;

        return $sql;

    }
}