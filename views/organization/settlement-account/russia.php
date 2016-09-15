<?php

use app\models\OrganizationSettlementAccount;

/** @var \app\models\Organization $organization */
/** @var \kartik\widgets\ActiveForm $form */

$typeId = OrganizationSettlementAccount::SETTLEMENT_ACCOUNT_TYPE_RUSSIA;
$settlementAccount = $organization->getSettlementAccount($typeId);
?>

<div class="row">
    <div class="col-sm-12">
        <div class="col-sm-12">
            <?php
            echo $form
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
                ->field($settlementAccount, 'bank_account[' . $typeId . ']')
                ->textInput(['value' => $settlementAccount->bank_account])
                ->label('Расчетный счет')
            ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <div class="col-sm-12">
            <?= $form
                ->field($settlementAccount, 'bank_bik[' . $typeId . ']')
                ->textInput([
                    'value' => $settlementAccount->bank_bik,
                    'class' => 'search-bik'
                ])
                ->label('БИК')
            ?>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="col-sm-12">
            <?= $form
                ->field($settlementAccount, 'bank_correspondent_account[' . $typeId . ']')
                ->textInput(['value' => $settlementAccount->bank_correspondent_account])
                ->label('Кор. счет')
            ?>
        </div>
    </div>
</div>
