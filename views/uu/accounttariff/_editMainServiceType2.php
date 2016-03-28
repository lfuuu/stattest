<?php
/**
 * свойства услуги для телефонии
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 * @var bool $isReadOnly
 */

$viewParams = [
    'formModel' => $formModel,
    'isReadOnly' => $isReadOnly
];

$accountTariff = $formModel->accountTariff;
if ($accountTariff->isNewRecord) {

    // персональная форма
    echo $this->render('_editMainVoip', $viewParams);

} else {

    // типовая форма

    // основная форма
    echo $this->render($isReadOnly ? '_viewMain' : '_editMain', $viewParams);

    // лог тарифов
    echo $accountTariff->isNewRecord ? '' : $this->render('_editLogGrid', $viewParams);

}