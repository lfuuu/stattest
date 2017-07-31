<?php

use yii\helpers\ArrayHelper;

return ArrayHelper::merge(include("../en-US/biller.php"), [
    'Replenishment of the account {account} for the amount of {sum} {currency}' => 'Dem Kontoguthaben von {account} wurden {sum} {currency} hinzugefügt.'
]);