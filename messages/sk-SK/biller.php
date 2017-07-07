<?php

use yii\helpers\ArrayHelper;

return ArrayHelper::merge(include("../en-EN/biller.php"), [
    'Replenishment of the account {account} for the amount of {sum} {currency}' => 'Na zákaznickom účte {account} bol dobytí kredit na sumu {sum} {currency}.'
]);