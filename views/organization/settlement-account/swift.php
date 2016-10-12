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
        <?php
        if (isset(OrganizationSettlementAccount::$settlementAccountByCurrency[$typeId])) {
            foreach (OrganizationSettlementAccount::$settlementAccountByCurrency[$typeId] as $currency) {
                $property = $settlementAccount->getProperty('bank_account_' . $currency);
                echo $this->render('bank_account', [
                    'form' => $form,
                    'typeId' => $typeId,
                    'model' => $property,
                    'currency' => $currency,
                    'label' => 'Номер счета',
                ]);
            }
        }
        ?>
    </div>

</div>
