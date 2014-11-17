<?php
namespace app\dao;

use app\classes\bill\SubscriptionCalculator;
use app\classes\Singleton;
use app\models\ClientAccount;

/**
 * @method static BillDao me($args = null)
 * @property
 */
class BillDao extends Singleton
{

    public function updateSubscriptionForAllClientAccounts()
    {
        session_write_close();
        set_time_limit(0);

        $accounts =
            ClientAccount::find()
                ->select('id')
                ->andWhere("status not in ( 'closed', 'trash', 'once', 'tech_deny', 'double', 'deny')")
                ->andWhere("credit > -1")
                ->all()
        ;

        foreach ($accounts as $account) {
            $this->updateSubscription($account->id);
        }
    }

    public function updateSubscription($clientAccountId)
    {
        $lastAccountDate = ClientAccount::dao()->getLastBillDate($clientAccountId);

        SubscriptionCalculator::create()
            ->setClientAccountId($clientAccountId)
            ->calculate($lastAccountDate)
            ->save();
    }

}