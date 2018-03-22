<?php
/**
 * Форма для применения групповых действий к отфильтрованным элементам
 *
 * @var \app\classes\BaseView $this
 * @var AccountTariffFilter $filterModel
 */

use app\modules\uu\filter\AccountTariffFilter;
use app\modules\uu\forms\AccountTariffEditForm;

// Для УУ с одним тарифом список доступных тарифов одинаковый.
// Достаточно взять список тарифов от первой попавшейся УУ.
if (
    $filterModel->tariff_period_id <= 0 ||
    !$accountTariffFirst = $filterModel->search()->query->one()
) {
    return '';
} ?>

<div class="well">
    <?= $this->render('_editLogForm', [
        'formModel' => new AccountTariffEditForm([
            'id' => $accountTariffFirst->id
        ]),
    ]); ?>
</div>