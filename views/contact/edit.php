<?php
/**
 * Редактирование контактов
 *
 * @var \yii\web\View $this
 * @var int $id
 * @var ClientContact[] $contacts
 */

use app\models\ClientContact;
use app\widgets\TabularInput\TabularInput;
use kartik\editable\Editable;
use kartik\form\ActiveForm;
use unclead\widgets\TabularColumn;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Аккаунт', 'url' => $cancelUrl = Url::to(['client/view', 'id' => $id])],
        ['label' => $this->title = 'Контакты', 'url' => Url::to(['contact/edit', 'id' => $id])],
    ],
]) ?>

<div class="contacts-edit">
    <?php $f = ActiveForm::begin() ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButtonSave') ?>
    </div>

    <?php
    $clientContactAttributeLabels = (new ClientContact)->attributeLabels();
    if (!$contacts) {
        // нет моделей, но виджет для рендеринга их обязательно требует
        $contact = new ClientContact;
        // Проинциализировать дефолтные значения из rules.
        // Наверняка, будет ошибка валидации (ибо какие-то поля не заполнены), но это не важно
        $contact->validate();
        $contacts = [$contact];
    }
    ?>
    <?= TabularInput::widget([
            'models' => array_values($contacts), // ключ должен быть автоинкрементный
            'min' => count($contacts), // уже созданные удалить нельзя. Можно только выключить
            'rowOptions' => function ($clientContact = null) {
                $options = [];

                /** @var ClientContact $clientContact */
                if ($clientContact) {
                    $options['class'] = '';
                    $options['class'] .= ($clientContact->is_validate ? ($clientContact->isEmail() ? ' info' : ' warning') : ' danger');
                    if (!$clientContact->is_active) {
                        $options['class'] .= ' row-inactive'; // input-sm
                    }
                }

                return $options;
            },
            'attributeOptions' => [
                'enableAjaxValidation' => true,
                // 'enableClientValidation' => true,
                'validateOnChange' => true,
                'validateOnSubmit' => true,
                'validateOnBlur' => true,
            ],
            'columns' => [
                [
                    'name' => 'is_official',
                    'title' => $clientContactAttributeLabels['is_official'],
                    'type' => TabularColumn::TYPE_CHECKBOX,
                ],
                [
                    'name' => 'type',
                    'title' => $clientContactAttributeLabels['type'],
                    'type' => Editable::INPUT_SELECT2,
                    'options' => [
                        'data' => ClientContact::$types,
                    ],
                ],
                [
                    'name' => 'data',
                    'title' => $clientContactAttributeLabels['data'],
                ],
                [
                    'name' => 'comment',
                    'title' => $clientContactAttributeLabels['comment'],
                ],
                [
                    'name' => 'user_id',
                    'title' => 'Создал',
                    'type' => TabularColumn::TYPE_STATIC,
                    'value' => function (ClientContact $clientContact) {
                        return
                            ($clientContact->user ? $clientContact->user->name : '') . '<br/>' .
                            $clientContact->ts;
                    },
                ],
                [
                    'name' => 'id', // чтобы идентифицировать модель
                    'type' => TabularColumn::TYPE_HIDDEN_INPUT,
                ],
                [
                    'name' => 'is_active',
                    'title' => $clientContactAttributeLabels['is_active'],
                    'type' => TabularColumn::TYPE_CHECKBOX,
                ],
            ],
        ]
    )
    ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButtonSave') ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?= $this->render('//layouts/_showHistory', ['model' => $contacts, 'deleteModel' => [new ClientContact(), 'client_id', $id]]) ?>

<style>
    .row-inactive, .row-inactive .select2-selection__rendered, .row-inactive input {
        color: #ccc !important;
    <?php // псевдо-запрет редактирование ?> pointer-events: none;
    }

    .row-inactive .list-cell__button,
    .row-inactive .list-cell__is_active input {
    <?php // псевдо-разрешение редактирование ?> pointer-events: auto;
    }

    .contacts-edit .list-cell__is_official,
    .contacts-edit .list-cell__is_active {
        text-align: center;
    }
</style>