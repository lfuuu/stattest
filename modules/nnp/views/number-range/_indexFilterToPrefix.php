<?php
/**
 * Диапазон номеров. Конвертировать фильтры в префиксы
 *
 * @var app\classes\BaseView $this
 */

use kartik\form\ActiveForm;

?>

<?php
$form = ActiveForm::begin();
$viewParams = [
    'form' => $form,
];
?>

<div class="well">
    <div class="row">
        <div class="col-sm-8">

            <?= $this->render('//layouts/_submitButton', [
                'text' => 'Конвертировать фильтры в префиксы',
                'glyphicon' => 'glyphicon-repeat',
                'params' => [
                    'name' => 'filterToPrefixButton',
                    'value' => 1,
                    'class' => 'btn btn-warning',
                    'aria-hidden' => 'true',
                    'onClick' => sprintf('return confirm("%s");', 'Все префиксы будут заменены текущими диапазонами на основе фильтров. Это необратимо. Продолжить?'),
                ],
            ]) ?>
        </div>
        <div class="col-sm-4">
            <div class="well">
                <div class="row">
                    <div class="col-12"> Обновить в <?= \app\classes\Html::a('mcn.ru/nnp', 'https://mcn.ru/nnp', ['target' => '_blank']) ?>:
                    </div>
                    <div>
                        <div class="row">
                            <div class="col-sm-6">

                                <?= $this->render('//layouts/_submitButton', [
                                    'text' => 'названия операторов',
                                    'glyphicon' => 'glyphicon-repeat',
                                    'params' => [
                                        'name' => 'nnpToRedisOperatorButton',
                                        'value' => 1,
                                        'class' => 'btn btn-info',
                                        'aria-hidden' => 'true',
                                    ],
                                ]) ?>
                            </div>
                            <div class="col-sm-6">

                                <?= $this->render('//layouts/_submitButton', [
                                    'text' => 'все названия',
                                    'glyphicon' => 'glyphicon-repeat',
                                    'params' => [
                                        'name' => 'nnpToRedisAllButton',
                                        'value' => 1,
                                        'class' => 'btn btn-info',
                                        'aria-hidden' => 'true',
                                    ],
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
