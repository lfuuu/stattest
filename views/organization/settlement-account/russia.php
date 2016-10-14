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

<?php if (isset(OrganizationSettlementAccount::$currencyBySettlementAccountTypeId[$typeId])): ?>
    <?php
    foreach(OrganizationSettlementAccount::$currencyBySettlementAccountTypeId[$typeId] as $currency):
        $property = $settlementAccount->getProperty('bank_account_' . $currency);
        ?>
        <div class="row">
            <div class="col-sm-12">
                <?= $this->render('bank_account', [
                    'form' => $form,
                    'typeId' => $typeId,
                    'model' => $property,
                    'currency' => $currency,
                    'label' => 'Расчетный счет',
                ])?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

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
