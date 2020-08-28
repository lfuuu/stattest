<?php

namespace app\classes\monitoring;

use app\classes\Assert;
use app\classes\Singleton;
use DateTimeImmutable;

/**
 * @method static ChangedBillsMonitor me($args = null)
 */
class ChangedBillsMonitor extends Singleton
{
    public function getData(\DateTimeImmutable $startDate, $isAll = false)
    {

        $date = $startDate->format('Y-m');
        $date_ = $startDate->format('Ym');

        $sql = <<<SQL
    select b.client_id, b.bill_no, sum, 

replace(replace(replace(replace(replace(ll1.comment, 'Счет создан.',''),'Счет обновлен.', ''), 'Сумма: ', ''), ' ', ''),',', '.')+0 as c1 , 
replace(replace(replace(replace(replace(ll2.comment, 'Счет создан.',''),'Счет обновлен.', ''), 'Сумма: ', ''), ' ', ''),',', '.')+0 as c2 

from newbills b
left join (
SELECT bill_no, max(id) as mid1 FROM `log_newbills` where 1
and bill_no like '{$date_}-%' 
and ts < '{$date}-02 00:00:00' 
and comment like '%Сумма%' 
and comment not like '%Сумма: 0,00%'
group by bill_no
) l1 on (b.bill_no = l1.bill_no)
left join log_newbills ll1 on (l1.mid1 = ll1.id)

left join (
SELECT bill_no, max(id) as mid1 FROM `log_newbills` where 1
and bill_no like '{$date_}-%' 
and ts > '{$date}-02 00:00:00' 
and comment like '%Сумма%' 
and comment not like '%Сумма: 0,00%'
group by bill_no
) l2 on (b.bill_no = l2.bill_no)
left join log_newbills ll2 on (l2.mid1 = ll2.id)
where b.bill_no like '{$date_}-%'
having c1 is not null and c2 is not null
and c1 != c2
order by  abs(abs(c1)-abs(c2)) desc
SQL;
        $data = \Yii::$app->db->createCommand($sql)->queryAll();

        return $data;
    }
}
