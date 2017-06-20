<?php
/**
 * Выбор страны (шаг 1/3)
 *
 * @var app\classes\BaseView $this
 * @var CountryFilter $country
 */

use app\modules\nnp\filter\CountryFilter;
use app\modules\nnp\models\Country;
use kartik\widgets\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$countries = CountryFilter::getList(true);
unset($countries[Country::RUSSIA]);
?>

<?= app\classes\Html::formLabel($this->title = 'Импорт. Выбор страны (шаг 1/3)') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title, 'url' => '/nnp/import/'],
    ],
]) ?>

<h2>Выберите страну</h2>
<div class="well">
    <?php
    $form = ActiveForm::begin([
        'action' => '/nnp/import/',
    ]);
    ?>

    <div class="row">
        <div class="col-sm-4">
            <?= $form->field($country, 'code')
                ->widget(Select2::className(), [
                    'data' => $countries,
                ])
                ->label(false) ?>
            Россия загружается автоматически из Россвязи.<br>
            Другие страны загружаются из файлов вручную.
        </div>

        <div class="col-sm-4">
            <?= $this->render('//layouts/_submitButton', [
                'text' => 'Загрузить или выбрать файл',
                'glyphicon' => 'glyphicon-step-forward',
                'params' => [
                    'class' => 'btn btn-success',
                ],
            ]) ?>
        </div>

    </div>

    <?php ActiveForm::end(); ?>
</div>

