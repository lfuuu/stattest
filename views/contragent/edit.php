<?php
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;

?>

<h1>
    Редактировать контрагента
</h1>

<?php
    $f = ActiveForm::begin(["type" => ActiveForm::TYPE_VERTICAL]);
?>

<?php
    echo Form::widget([
        'model' => $model,
        'form' => $f,
        'columns' => 1,
        'attributes' => [
            'legal_type' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => ['legal' => 'Юр. лицо', "ip" => "ИП", "person" => "Физ.лицо"]],
            'name' => ['type' => Form::INPUT_TEXT],
            'name_full' => ['type' => Form::INPUT_TEXT],
            'address_jur' => ['type' => Form::INPUT_TEXT],
            'address_post' => ['type' => Form::INPUT_TEXT],
            'inn' => ['type' => Form::INPUT_TEXT],
            'inn_euro' => ['type' => Form::INPUT_TEXT],
            'kpp' => ['type' => Form::INPUT_TEXT],
            'position' => ['type' => Form::INPUT_TEXT],
            'fio' => ['type' => Form::INPUT_TEXT],
            'tax_regime' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => ['full' => 'Полный', "6" => "Упрощенный 6", "15" => "Упрощенный 15"]],
            'ogrn' => ['type' => Form::INPUT_TEXT],
            'opf' => ['type' => Form::INPUT_TEXT],
            'okpo' => ['type' => Form::INPUT_TEXT],
            'okvd' => ['type' => Form::INPUT_TEXT],

        ],
    ]);
?>


<?php ActiveForm::end(); ?>
