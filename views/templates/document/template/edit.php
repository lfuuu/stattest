<?php

use app\assets\TinymceAsset;
use app\classes\Html;
use app\models\document\DocumentFolder;
use app\models\document\DocumentTemplate;
use app\widgets\JQTree\JQTreeInput;
use kartik\widgets\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

TinymceAsset::register(Yii::$app->view);

$cancelUrl = Url::toRoute(['/templates/document/template']);

/** @var $dataProvider ActiveDataProvider */
/** @var DocumentTemplate $model */

echo Html::formLabel('Управление шаблонами документов');

echo Breadcrumbs::widget([
    'links' => [
        'Шаблоны',
        [
            'label' => 'Управление шаблонами документов',
            'url' => $cancelUrl,
        ],
        'Редактирование документа'
    ],
]);

$form = ActiveForm::begin([]);
?>

<div class="row">
    <div class="col-sm-4">
        <?= $form->field($model, 'name')->textInput() ?>
    </div>
    <div class="col-sm-4">
        <?= $form->field($model, 'folder_id')->widget(JQTreeInput::class, [
            'data' => new DocumentFolder,
            'htmlOptions' => [
                'id' => 'treeview-input',
            ],
        ])
        ?>
    </div>
    <div class="col-sm-3">
        <?= $form->field($model, 'type')->dropDownList(\app\models\ClientDocument::$types) ?>
    </div>
    <div class="col-sm-1">
        <?= $form->field($model, 'sort')->textInput() ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <?= $form->field($model, 'content')->textarea(['style' => 'height: 600px;']) ?>
    </div>
</div>

<?php // кнопки ?>
<div class="form-group text-right">
    <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
    <?= $this->render('//layouts/_submitButtonSave') ?>
</div>

<?php
echo $form->field($model, 'id')->hiddenInput()->label('');
ActiveForm::end();
?>

<table border="1" style="width: 100%; margin-top: 20px;">
    <tr>
        <th>Переменная</th>
        <th>Описание</th>
    </tr>
    <tr>
        <td>{$position}</td>
        <td>Должность Исполнительного органа</td>
    </tr>
    <tr>
        <td>{$fio}</td>
        <td>Фио Исполнительного органа</td>
    </tr>
    <tr>
        <td>{$first_name}</td>
        <td>Имя</td>
    </tr>
    <tr>
        <td>{$last_name}</td>
        <td>Фамилия</td>
    </tr>
    <tr>
        <td>{$middle_name}</td>
        <td>Отчество</td>
    </tr>
    <tr>
        <td>{$birthdate}</td>
        <td>День рождения</td>
    </tr>
    <tr>
        <td>{$birthplace}</td>
        <td>Место рождения</td>
    </tr>
    <tr>
        <td>{$name}</td>
        <td>Краткое наименование контрагента</td>
    </tr>
    <tr>
        <td>{$name_full}</td>
        <td>Полное наименование контрагента</td>
    </tr>
    <tr>
        <td>{$address_jur}</td>
        <td>Адрес юридический</td>
    </tr>
    <tr>
        <td>{$bank_properties}</td>
        <td>Банковские реквизиты</td>
    </tr>
    <tr>
        <td>{$bik}</td>
        <td>БИК</td>
    </tr>
    <tr>
        <td>{$address_post_real}</td>
        <td>Действительный почтовый адрес</td>
    </tr>
    <tr>
        <td>{$address_post}</td>
        <td>Почтовый адрес</td>
    </tr>
    <tr>
        <td>{$corr_acc}</td>
        <td>К/С</td>
    </tr>
    <tr>
        <td>{$pay_acc}</td>
        <td>Р/С</td>
    </tr>
    <tr>
        <td>{$inn}</td>
        <td>ИНН</td>
    </tr>
    <tr>
        <td>{$kpp}</td>
        <td>КПП</td>
    </tr>
    <tr>
        <td>{$stamp}</td>
        <td>Печатать штамп (1 | 0)</td>
    </tr>
    <tr>
        <td>{$legal_type}</td>
        <td>Тип контрагента(ip | legal | person)</td>
    </tr>
    <tr>
        <td>{$old_legal_type}</td>
        <td>Тип контрагента( priv | org)</td>
    </tr>
    <tr>
        <td>{$address_connect}</td>
        <td>Предполагаемый адрес подключения</td>
    </tr>
    <tr>
        <td>{$account_id}</td>
        <td>id ЛС</td>
    </tr>
    <tr>
        <td>{$bank_name}</td>
        <td>Название банка</td>
    </tr>
    <tr>
        <td>{$credit}</td>
        <td>Разрешить кредит</td>
    </tr>

    <tr>
        <td>{$contract_no}</td>
        <td>№ договора</td>
    </tr>
    <tr>
        <td>{$contract_date}</td>
        <td>Дата договора</td>
    </tr>
    <tr>
        <td>{$contract_dop_date}</td>
        <td>Дополнительная дата договора</td>
    </tr>
    <tr>
        <td>{$contract_dop_no}</td>
        <td>Дополнительный № договора</td>
    </tr>

    <tr>
        <td>{$emails}</td>
        <td>Email</td>
    </tr>
    <tr>
        <td>{$phones}</td>
        <td>Телефон</td>
    </tr>
    <tr>
        <td>{$faxes}</td>
        <td>Факс</td>
    </tr>

    <tr>
        <td>{$organization_firma}</td>
        <td>Организация( mcn | mcn-telecom | ... )</td>
    </tr>
    <tr>
        <td>{$organization_director_post}</td>
        <td>Почтовый адрес директора</td>
    </tr>
    <tr>
        <td>{$organization_director}</td>
        <td>ФИО директора</td>
    </tr>
    <tr>
        <td>{$organization_name}</td>
        <td>Название организации( ООО «МСН Телеком» | ООО «Эм Си Эн» | ... )</td>
    </tr>
    <tr>
        <td>{$organization_address}</td>
        <td>Адрес организации</td>
    </tr>
    <tr>
        <td>{$organization_inn}</td>
        <td>ИНН организации</td>
    </tr>
    <tr>
        <td>{$organization_kpp}</td>
        <td>КПП организации</td>
    </tr>
    <tr>
        <td>{$organization_corr_acc}</td>
        <td>К/С организации</td>
    </tr>
    <tr>
        <td>{$organization_bik}</td>
        <td>БИК организации</td>
    </tr>
    <tr>
        <td>{$organization_bank}</td>
        <td>Банк организации</td>
    </tr>
    <tr>
        <td>{$organization_phone}</td>
        <td>Телефон организации</td>
    </tr>
    <tr>
        <td>{$organization_email}</td>
        <td>Email организации</td>
    </tr>
    <tr>
        <td>{$organization_pay_acc}</td>
        <td>Р/С организации</td>
    </tr>
    <tr>
        <td>{$firm_detail_block}</td>
        <td>
            Платежные реквизиты организации: <br/>
                            <pre>
$f["name"] . "<br/> Юридический адрес: " . $f["address"] .
(isset($f["post_address"]) ? "<br/> Почтовый адрес: " . $f["post_address"] : "")
. " ИНН " . $f["inn"] . ", КПП " . $f["kpp"]
. ($b ?
"  Банковские реквизиты:"
. " р/с:&nbsp;" . $f["acc"] . " в " . $f["bank_name"]
. " к/с:&nbsp;" . $f["kor_acc"]
. " БИК:&nbsp;" . $f["bik"]
: '')
. " телефон: " . $f["phone"]
. (isset($f["fax"]) && $f["fax"] ? "<br/> факс: " . $f["fax"] : "")
. " е-mail: " . $f["email"];
                            </pre>

        </td>
    </tr>
    <tr>
        <td>{$payment_info}</td>
        <td>

            Платежные реквизиты контрагента: <br/>
                            <pre>
                            $result = $contragent->name_full . '<br/>Адрес: ' . (
                                $contragent->legal_type == 'person'
                                ? $contragent->person->registration_address
                                : $account->address_jur
                            ) . '<br/>';

                            if ($contragent->legal_type == 'person') {
                                if (!empty($account->bank_properties))
                                    return $result . nl2br($account->bank_properties);
                                return
                                    $result .
                                    'Паспорт серия ' . $contragent->person->passport_serial .
                                    ' номер ' . $contragent->person->passport_number .
                                    '<br/>Выдан: ' . $contragent->person->passport_issued .
                                    '<br/>Дата выдачи: ' . $contragent->person->passport_date_issued . ' г.' .
                                    (
                                        count($officialContacts)
                                            ? '<br />E-mail: ' . implode('; ', $officialContacts['email'])
                                            : ''
                                    );
                            }
                            else {
                                return
                                    $result .
                                    'Банковские реквизиты: ' .
                                    'р/с ' . ($account->pay_acc ?: '') . '<br/>' .
                                    $account->bank_name . ' ' . $account->bank_city  .
                                    ($account->corr_acc ? '<br/>к/с ' . $account->corr_acc : '') .
                                    ', БИК ' . $account->bik .
                                    ', ИНН ' . $contragent->inn .
                                    ', КПП ' . $contragent->kpp .
                                    (!empty($account->address_post_real) ? '<br/>Почтовый адрес: ' . $account->address_post_real : '') .
                                    (
                                        count($officialContacts)
                                            ? '<br />E-mail: ' . implode('; ', $officialContacts['email'])
                                            : ''
                                    );
                            }
                            </pre>
        </td>
    </tr>
</table>