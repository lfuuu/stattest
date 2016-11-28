<?php

use app\classes\Html;
use app\models\filter\PartnerRewardsFilter;

/** @var PartnerRewardsFilter $filterModel */
?>

<div class="col-sm-12" style="padding-bottom: 20px;">
    <?php if (count($filterModel->contractsWithoutRewardSettings)): ?>
        <div class="col-sm-6 bg-danger">
            <fieldset style="padding: 5px;">
                <label>Отсутствуют настройки вознаграждений для договоров:</label>
                <ul>
                    <?php foreach($filterModel->contractsWithoutRewardSettings as $contract): ?>
                        <li>
                            <?= Html::a(
                                $contract['contragent_name'] . ' (#' . $contract['contract_id'] . ')',
                                ['contract/edit', 'id' => $contract['contract_id']],
                                ['target' => '_blank']
                            ) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </fieldset>
        </div>
    <?php endif; ?>

    <?php if (count($filterModel->contractsWithIncorrectBusinessProcess)): ?>
        <div class="col-sm-6 bg-danger">
            <fieldset style="padding: 5px;">
                <label>Договора с неправильным бизнес-процессом:</label>
                <ul>
                    <?php foreach($filterModel->contractsWithIncorrectBusinessProcess as $contract): ?>
                        <li>
                            <?= Html::a(
                                $contract['contragent_name'] . ' (#' . $contract['contract_id'] . ')',
                                ['contract/edit', 'id' => $contract['contract_id']],
                                ['target' => '_blank']
                            ) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </fieldset>
        </div>
    <?php endif; ?>
</div>