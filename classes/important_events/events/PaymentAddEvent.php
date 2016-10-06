<?php

namespace app\classes\important_events\events;

use app\classes\important_events\events\properties\BalanceProperty;
use app\classes\important_events\events\properties\ClientProperty;
use app\classes\important_events\events\properties\CurrencyProperty;
use app\classes\important_events\events\properties\DateProperty;
use app\classes\important_events\events\properties\SummaryProperty;
use app\classes\important_events\events\properties\UserProperty;

class PaymentAddEvent extends UnknownEvent
{

    public static
        $properties = [
            'date' => DateProperty::class,
            'client' => ClientProperty::class,
            'balance' => BalanceProperty::class,
            'sum' => SummaryProperty::class,
            'currency' => CurrencyProperty::class,
            'user_id' => UserProperty::class,
        ];

}