<?php

use app\models\Language;
use yii\helpers\ArrayHelper;

return ArrayHelper::merge(include('../' . Language::LANGUAGE_ENGLISH . '/biller.php'), [
    'Replenishment of the account {account} for the amount of {sum} {currency}' => 'Dem Kontoguthaben von {account} wurden {sum} {currency} hinzugefügt.'
]);