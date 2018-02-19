<?php
/**
 * Вознаграждения партнеров. Неправильные данные
 *
 * @var \app\classes\BaseView $this
 * @var PartnerRewardsFilter $filterModel
 */

use app\classes\Html;
use app\models\filter\PartnerRewardsFilter;

?>

<div class="row">

    <?php if (count($filterModel->contractsWithoutRewardSettings)) : ?>
        <div class="col-sm-6 bg-danger">
            <label>Отсутствуют настройки вознаграждений для договоров:</label>
            <ul>
                <?php foreach ($filterModel->contractsWithoutRewardSettings as $contract): ?>
                    <li>
                        <?= Html::a(
                            $contract['contragent_name'] . ' (#' . $contract['contract_id'] . ')',
                            ['contract/edit', 'id' => $contract['contract_id']],
                            ['target' => '_blank']
                        ) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (count($filterModel->contractsWithIncorrectBusinessProcess)) : ?>
        <div class="col-sm-6 bg-danger">
            <label>Договора с неправильным бизнес-процессом:</label>
            <ul>
                <?php foreach ($filterModel->contractsWithIncorrectBusinessProcess as $contract): ?>
                    <li>
                        <?= Html::a(
                            $contract['contragent_name'] . ' (#' . $contract['contract_id'] . ')',
                            ['contract/edit', 'id' => $contract['contract_id']],
                            ['target' => '_blank']
                        ) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

</div>