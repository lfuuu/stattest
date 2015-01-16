<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\ClientAccount;

/**
 * @method static ClientAccountDao me($args = null)
 * @property
 */
class ClientAccountDao extends Singleton
{

    public function getLastBillDate($clientAccountId)
    {
        return ClientAccount::getDb()->createCommand("
                select max(b.bill_date)
                from newbills b, newbill_lines bl
                where b.client_id=:clientAccountId and day(b.bill_date) = 1 and b.bill_no=bl.bill_no and bl.id_service > 0
            ",
            [':clientAccountId' => $clientAccountId]
        )->queryScalar();
    }

    public function getLastPayedBillMonth($clientAccountId)
    {
        return ClientAccount::getDb()->createCommand("
                select b.bill_date - interval day(b.bill_date)-1 day
                from newbills b left join newbill_lines bl on b.bill_no=bl.bill_no
                where b.client_id=:clientAccountId and b.is_payed=1 and bl.id_service > 0
                group by b.bill_date
                order by b.bill_date desc
                limit 1
            ",
            [':clientAccountId' => $clientAccountId]
        )->queryScalar();
    }

}
