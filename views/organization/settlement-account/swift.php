<?php

use app\models\OrganizationSettlementAccount;

/** @var \app\models\Organization $organization */
/** @var \kartik\widgets\ActiveForm $form */

$typeId = OrganizationSettlementAccount::SETTLEMENT_ACCOUNT_TYPE_SWIFT;
$settlementAccount = $organization->getSettlementAccount($typeId);
?>

<div class="row">
    <div class="col-sm-12">
        <div class="col-sm-12">
            <?= $form
                ->field($settlementAccount, 'bank_name[' . $typeId . ']')
                ->textInput(['value' => $settlementAccount->bank_name])
                ->label('Название банка')
            ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="col-sm-12">
            <?= $form
                ->field($settlementAccount, 'bank_address[' . $typeId . ']')
                ->textInput(['value' => $settlementAccount->bank_address])
                ->label('Адрес банка')
            ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <div class="col-sm-12">
            <?= $form
                ->field($settlementAccount, 'bank_bik[' . $typeId . ']')
                ->textInput(['value' => $settlementAccount->bank_bik])
                ->label('SWIFT')
            ?>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="col-sm-12">
            <?= $form->field($settlementAccount, 'bank_account[' . $typeId . ']')
                ->textInput(['value' => $settlementAccount->bank_account])
                ->label('Номер счета')
            ?>
        </div>
    </div>

</div>
