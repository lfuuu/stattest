<?php
/**
 * Очистить ресурсы телефонии и пересчитать их заново
 *
 * @var \app\classes\BaseView $this
 */

use app\classes\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tariffs'),
        ['label' => $this->title = Yii::t('tariff', 'Clear UU-calls'), 'url' => '/uu/resource/clear'],
    ],
]) ?>

<ol>
    <li><a href="/?module=voipnew&action=calls_recalc">Пересчитать звонки в CallsRaw</a>.<br/><br/></li>
    <li>Ресурсы-звонки...
        <ul>
            <li>
                <?php
                $form = ActiveForm::begin();
                echo Html::hiddenInput('isPrevMonth', 1);
                echo $this->render('//layouts/_submitButtonDrop');
                ActiveForm::end(); ?>
                &nbsp;за прошлый месяц. Или запустить из консоли <code>./yii ubiller/clear-resource-calls-prev-month</code>
                <div class="clearfix"></div>
                <br/>
            </li>
            <li>
                <?php
                $form = ActiveForm::begin();
                echo Html::hiddenInput('isThisMonth', 1);
                echo $this->render('//layouts/_submitButtonDrop');
                ActiveForm::end(); ?>
                &nbsp;за текущий месяц. Или запустить из консоли <code>./yii ubiller/clear-resource-calls-this-month</code>
                <div class="clearfix"></div>
                <br/>
            </li>
        </ul>
    </li>
    <li>Запустить из консоли <code>flock -w 3 /tmp/yii-ubiller ./yii ubiller</code> или подождать, пока оно запустится по cron автоматически, чтобы ресурсы посчитались заново и обновились счета.</li>
</ol>
