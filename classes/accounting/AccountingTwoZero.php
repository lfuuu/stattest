<?php

namespace app\classes\accounting;

use app\classes\accounting\accounting20\Totals;
use app\classes\accounting\accounting20\Lists;
use app\models\ClientAccount;

class AccountingTwoZero
{
    public ?Totals $totals = null;
    public ?Lists $list = null;

    public function __construct(ClientAccount $account)
    {
        $this->list = new Lists(['account' => $account]);
        $this->totals = new Totals(['lists' => $this->list, 'account' => $account]);
    }
}