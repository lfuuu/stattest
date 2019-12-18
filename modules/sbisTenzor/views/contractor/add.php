<?php

use app\classes\Html;
use app\modules\sbisTenzor\forms\contractor\AddForm;
use kartik\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

/**
 * @var \app\classes\BaseView $this
 * @var AddForm $form
 * @var string $indexUrl
 */

$this->title = $form->getTitle();

?>

<?php

echo Html::formLabel($this->title);
echo Breadcrumbs::widget([
    'links' => [
        'СБИС',
        ['label' => 'Роуминг', 'url' => $indexUrl],
        $this->title
    ],
]) ?>

<?php
$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
]);
?>
<div class="well" style="background-color: white;">
    <div class="row">
        <div class="col-sm-6">
            <label>Идентификатор ЭДО: </label>
            <input type="text" name="exchange_id" size="50" /><br /><br />
        </div>
    </div>

    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $indexUrl]) ?>
        <?= $this->render('//layouts/_submitButton', [
            'text' => Yii::t('common', 'Установить'),
            'glyphicon' => 'glyphicon-save',
            'params' => [
                'class' => 'btn btn-primary',
            ],
        ]) ?>
    </div>

</div>
<?php ActiveForm::end(); ?>
