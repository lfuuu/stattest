<?php
/**
 * Создание/редактирование универсального тарифа
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 */

use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$tariff = $formModel->tariff;

if (!$tariff->isNewRecord) {
    $this->title = $tariff->name;
} else {
    $this->title = Yii::t('common', 'Create');
}

$serviceType = $tariff->serviceType;
?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tariffs'),
        ['label' => $serviceType ? $serviceType->name : '', 'url' => Url::to(['uu/tariff', 'serviceTypeId' => $serviceType ? $serviceType->id : ''])],
        $this->title
    ],
]) ?>

<div class="resource-tariff-form">
    <?php
    $form = ActiveForm::begin();
    $viewParams = [
        'formModel' => $formModel,
        'form' => $form,
    ];
    ?>

    <?php // кнопка сохранения ?>
    <?= $this->render('_editSubmit', $viewParams) ?>

    <?php
    // сообщение об ошибке
    if ($formModel->validateErrors) {
        echo $this->render('//layouts/_alert', ['type' => 'danger', 'message' => $formModel->validateErrors]);
    }
    ?>

    <?php // свойства тарифа из основной таблицы ?>
    <?= $this->render('_editMain', $viewParams) ?>

    <?php // свойства тарифа конкретного типа услуги (ВАТС, телефония и пр.) ?>
    <?php
    $fileName = '_editServiceType' . $tariff->service_type_id;
    $fileNameFull = __DIR__ . '/' . $fileName . '.php';
    if (file_exists($fileNameFull)) {
        echo $this->render($fileName, $viewParams); // @todo
    }
    ?>

    <?php // свойства тарифа (периоды) ?>
    <?= $this->render('_editPeriod', $viewParams) ?>

    <?php // свойства тарифа (ресурсы) ?>
    <?= $this->render('_editResource', $viewParams) ?>

    <?php // кнопка сохранения ?>
    <?= $this->render('_editSubmit', $viewParams) ?>

    <?php ActiveForm::end(); ?>
</div>
