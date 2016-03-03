<?php
use yii\helpers\Html;
use app\assets\AppAsset;
/* @var $this \yii\web\View */

AppAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <base href="/" />
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
        html, body {
            background-color: #fff;
        }
    </style>
</head>
<body>

<?php $this->beginBody() ?>

    <?= $content ?>

<?php
// Это фикс бага с select2 v4. Он вызывается раньше инициализации, надо его вызывать позже.
// Правильнее это сделать в vendor/kartik-v/yii2-krajee-base/WidgetTrait.php::getPluginScript, но vendor менять не могу
// Мне стыдно за такой говнокод, но по-другому исправить не получается.
if ($this->js) {
    foreach ($this->js as &$scripts) {
        foreach ($scripts as &$script) {
            $script = preg_replace('/jQuery\.when\((.*?)\)\.done/', 'jQuery.when(  setTimeout(function(){$1},10)  ).done', $script);
        }
        unset($script);
    }
    unset($scripts);
}
?>

<?php $this->endBody() ?>

<script type="text/javascript">
jQuery(document).ready(function () {
    $('.select2').select2();
});
</script>

</body>
</html>
<?php $this->endPage() ?>
