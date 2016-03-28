<?php
/**
 * Создание/редактирование универсальной услуги
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 * @var bool $isReadOnly
 */


use app\classes\Html;
use app\models\Region;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

$accountTariff = $formModel->accountTariff;
$serviceType = $formModel->getServiceType();

?>

<div class="resource-tariff-form well">
    <?php $form = ActiveForm::begin(); ?>

    <?php // добавить тариф (только для новых записей) ?>
    <?= $accountTariff->isNewRecord ?
        $this->render('_editLogInput', [
            'formModel' => $formModel,
            'form' => $form,
        ]) :
        ''
    ?>

    <?php
    if (!$accountTariff->isNewRecord) {
        ?>
        <div class="row">

            <div class="col-sm-2">
                <label><?= $accountTariff->getAttributeLabel('insert_user_id') ?></label>
                <div><?= $accountTariff->insertUser ?
                        $accountTariff->insertUser->name :
                        Yii::t('common', '(not set)')
                    ?></div>
            </div>

            <div class="col-sm-2">
                <label><?= $accountTariff->getAttributeLabel('insert_time') ?></label>
                <div><?= $accountTariff->insert_time ?></div>
            </div>


            <div class="col-sm-2">
                <label><?= $accountTariff->getAttributeLabel('update_user_id') ?></label>
                <div><?= $accountTariff->updateUser ?
                        $accountTariff->updateUser->name :
                        Yii::t('common', '(not set)')
                    ?></div>
            </div>

            <div class="col-sm-2">
                <label><?= $accountTariff->getAttributeLabel('update_time') ?></label>
                <div><?= $accountTariff->update_time ?: Yii::t('common', '(not set)') ?></div>
            </div>

            <div class="col-sm-2">
                <label><?= $accountTariff->getAttributeLabel('client_account_id') ?></label>
                <div><?= Html::a(
                        Html::encode($accountTariff->clientAccount->client),
                        ['/client/view', 'id' => $accountTariff->client_account_id]
                    ) ?></div>
            </div>

            <div class="col-sm-2">
                <label><?= Yii::t('tariff', 'Non-universal service') ?></label>
                <div><?= $accountTariff->getNonUniversalUrl() ?></div>
            </div>

        </div>
        <br/>
        <?php
    }
    ?>

    <div class="row">

        <div class="col-sm-6">
            <?= $form->field($accountTariff, 'comment')
                ->textarea()
                ->render()
            ?>
        </div>

        <div class="col-sm-3">
            <?= $form->field($accountTariff, 'region_id')
                ->widget(Select2::className(), [
                    'data' => Region::getList(true),
                ]) ?>
        </div>

    </div>

    <?php
    if ($accountTariff->prev_account_tariff_id) {
        ?>
        <div class="row">
            <div class="col-sm-6">
                <?php
                $fieldPrevAccountTariff = $form->field($accountTariff, 'prev_account_tariff_id');
                $fieldPrevAccountTariff->parts['{input}'] =
                    Html::tag(
                        'div',
                        $accountTariff->prevAccountTariff ?
                            Html::a(
                                Html::encode($accountTariff->prevAccountTariff->getName()),
                                $accountTariff->prevAccountTariff->getUrl()
                            ) :
                            Yii::t('common', '(not set)')
                    );
                echo $fieldPrevAccountTariff->render()
                ?>
            </div>
        </div>
        <?php
    }
    ?>

    <?php // свойства услуги конкретного типа услуги (ВАТС, телефония и пр.) ?>
    <?php
    $fileName = '_editServiceType' . $accountTariff->service_type_id;
    $fileNameFull = __DIR__ . '/' . $fileName . '.php';
    if (file_exists($fileNameFull)) {
        $viewParams = [
            'formModel' => $formModel,
            'form' => $form,
        ];
        echo $this->render($fileName, $viewParams);
    }
    ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('common', $accountTariff->isNewRecord ? 'Create' : 'Save'), ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
