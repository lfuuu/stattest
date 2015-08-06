<?php
use \kartik\widgets\ActiveForm;
use \app\models\ClientDocument;
use \kartik\builder\Form;

\app\assets\TinymceAsset::register(Yii::$app->view);
?>

<h2><?= ClientDocument::$types[$model->type] ?></h2>

<?php $f = ActiveForm::begin([]); ?>


<?php if ($model->type != 'blank'): ?>
    <div class="row">
        <div class="col-sm-2">
            <?= $f->field($model, 'contract_no')->input(Form::INPUT_TEXT, ['value' => $model->type == 'contract' ? $model->contract_no : $model->contract_dop_no]) ?>
        </div>
        <div class="col-sm-2">
            <?=
            $f->field($model, 'contract_date')->widget('\kartik\widgets\DatePicker', [
                    'value' => $model->type == 'contract' ? date('Y-m-d', strtotime($model->contract_date)) : date('Y-m-d', strtotime($model->contract_dop_date)),
                    'removeButton' => false,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                    ],
            ]);
            ?>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-sm-4">
        <div class="row">
            <div class="col-sm-12">
                <?= $f->field($model, 'comment')->input() ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <?php $model->content = $model->getFileContent() ?>
        <?= $f->field($model, 'content')->textarea(['style' => 'height: 600px;']) ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-2">
        <button type="submit" class="btn btn-primary col-sm-12">Сохранить</button>
    </div>
</div>
<?php ActiveForm::end(); ?>

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