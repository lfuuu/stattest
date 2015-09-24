<?php
use \kartik\widgets\ActiveForm;
use \app\models\ClientDocument;
use \kartik\builder\Form;

\app\assets\TinymceAsset::register(Yii::$app->view);
?>

<h2>Редактирование шаблона</h2>

<?php $f = ActiveForm::begin([]); ?>

<div class="row">
    <div class="col-sm-3">
        <?= $f->field($model, 'type')->dropDownList(ClientDocument::$types) ?>
    </div>
    <div class="col-sm-3">
        <?= $f->field($model, 'folder_id')->dropDownList(\app\models\document\DocumentFolder::getList()) ?>
    </div>
    <div class="col-sm-3">
        <?= $f->field($model, 'id')->dropDownList(\app\models\document\DocumentTemplate::getList()) ?>
    </div>
    <div class="col-sm-3" style="padding-top: 20px;">
        <?= \yii\helpers\Html::button('<i class="glyphicon glyphicon-plus"></i> Добавить', [
            'class' => 'btn btn-success',
            'onclick' => 'return showIframePopup(this);',
            'href' => \yii\helpers\Url::toRoute(['document/template/edit-form']),
        ]) ?>
    </div>
</div>
<?php if ($model->id): ?>
    <div class="row">
        <div class="col-sm-12">
            <?= $f->field($model, 'content')->textarea(['style' => 'height: 600px;']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <?= \yii\helpers\Html::button('Сохранить', [
                'class' => 'btn btn-primary',
                'type' => 'submit',
            ]) ?>
            <?= \yii\helpers\Html::button('Изменить параметры документа', [
                'class' => 'btn btn-info pull-right',
                'onclick' => 'return showIframePopup(this);',
                'href' => \yii\helpers\Url::toRoute(['document/template/edit-form', 'id' => $model->id]),
            ]) ?>
        </div>
    </div>


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
            <td>{$contact}</td>
            <td>ФИО контактного лица</td>
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
                                        '<br/>Дата выдачи: ' . $contragent->person->passport_date_issued . ' г.';
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
                                        (!empty($account->address_post_real) ? '<br/>Почтовый адрес: ' . $account->address_post_real : '');
                                }
                                </pre>
            </td>
        </tr>
    </table>
<?php endif; ?>


<script type="text/javascript">
    $(document).ready(function () {
        tinymce.init({
            selector: "textarea",
            plugins: [
                "advlist autolink lists link image charmap print preview anchor",
                "searchreplace visualblocks code fullscreen",
                "insertdatetime media table contextmenu paste"
            ],
            toolbar: "insertfile undo redo | styleselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
        });
    });
</script>

<script>
    var templates = <?= json_encode(\app\dao\ClientDocumentDao::templateList()) ?>;
    var s1 = $('#documenttemplate-type');
    var s2 = $('#documenttemplate-folder_id');
    var s3 = $('#documenttemplate-id');
    var currentTplId = s3.val();
    var generateList = function () {
        s3.empty();
        $.each(templates, function (k, v) {
            if (v['folder_id'] == s2.val() && v['type'] == s1.val())
                var o = s3.append('<option value="' + v['id'] + '" ' + (v['id'] == currentTplId ? 'selected' : '') + '>' + v['name'] + '</option>');
        });
    };

    $(function () {
        s1.add(s2).on('change', generateList).trigger('change');
        s1.add(s2).add(s3).on('change', function () {
            if (s3.val() != currentTplId && s3.val())
                window.location.search = 'id=' + s3.val();
        });
    });
</script>
