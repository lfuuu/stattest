<?php
/**
 * Редактирование контактов
 *
 * @var \app\classes\BaseView $this
 * @var ClientAccount $account
 * @var ClientContact[] $contacts
 */

use app\models\ClientAccount;
use app\models\ClientContact;
use app\widgets\TabularInput\TabularInput;
use kartik\form\ActiveForm;
use unclead\multipleinput\TabularColumn;

?>

<div id="contacts-edit">
    <?php $f = ActiveForm::begin() ?>

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
            'min' => 0,
            'rowOptions' => function ($clientContact = null) {
                $options = [];

                /** @var ClientContact $clientContact */
                if ($clientContact) {
                    $options['class'] = 'form-group-sm';
                    $options['class'] .= ($clientContact->is_validate ? ($clientContact->isEmail() ? ' info' : ' warning') : ' danger');
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
                    'type' => TabularColumn::TYPE_DROPDOWN,
                    'items' => ClientContact::$types,
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
            ],
        ]
    )
    ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_submitButtonSave') ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?= $this->render('//layouts/_showHistory', [
    'parentModel' => [new ClientContact(), $account->id],
]) ?>

<style>
    .row-inactive, .row-inactive .select2-selection__rendered, .row-inactive input {
        color: #aaa !important;
    }

    #contacts-edit .list-cell__is_official {
        text-align: center;
    }

    #contacts-edit td {
        padding: 0 5px;
    }
</style>