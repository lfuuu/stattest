<?php
use \kartik\widgets\ActiveForm;
use \app\models\ClientDocument;
use \kartik\builder\Form;

\app\assets\TinymceAsset::register(Yii::$app->view);
?>

<h2><?= ClientDocument::$types[$model->type] ?></h2>

<?php $f = ActiveForm::begin([]); ?>

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
            relative_urls: false,
            plugins: [
                "advlist autolink lists link image charmap print preview anchor",
                "searchreplace visualblocks code fullscreen",
                "insertdatetime media table contextmenu paste"
            ],
            toolbar: "insertfile undo redo | styleselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
        });
    });
</script>