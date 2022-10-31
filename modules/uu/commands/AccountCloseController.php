<?php

namespace app\modules\uu\commands;

use app\exceptions\ModelValidationException;
use app\models\DidGroup;
use app\modules\nnp\models\NdcType;
use app\modules\uu\filter\AccountTariffFilter;
use app\modules\uu\forms\DisableForm;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffFlat;
use app\modules\uu\models\AccountTrouble;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffStatus;
use yii\console\Controller;
use app\models\Currency;
use app\models\City;
use app\models\Region;

class AccountCloseController extends Controller
{
    public function actionSet($accountId, $from = null)
    {
        if (!$from) {
            $from = date("Y-m-d", strtotime('first day of next month'));
        }

        $form = new DisableForm();

        if (!$form->load([
            'date' => $from,
            'clientAccountId' => $accountId,
            'code' => (string)$form->generateCode(),
        ], '') || !$form->validate()) {
            throw new \InvalidArgumentException(implode(", ", $form->getFirstErrors()));
        }

        echo strip_tags($form->go());
    }
}
