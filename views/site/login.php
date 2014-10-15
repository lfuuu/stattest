<?php
use yii\helpers\Html;
use app\assets\AppAsset;
use \yii\widgets\ActiveForm;

/* @var $this \yii\web\View */
/* @var $content string */
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
</head>
<body>
<?php $this->beginBody() ?>

<div style="width: 350px; margin: 0 auto; padding-top: 50px">
    <div style="text-align: center">
        <div class="site_caption" style="float: none; display: inline">
            <a href="/" class="logo"></a>
            <div class="message">Сервер статистики</div>
        </div>
    </div>

    <?= Html::beginForm() ?>
    <div style="width: 350px; text-align: center">
        <h2>Operator login</h2>
        <table cellSpacing=4 cellPadding=2 width="100%" border=0 style="border: 1px solid #E0E0E0; background-color: #F7F7F7;">
            <tr>
                <td colspan="2">
                    <?php
                    foreach ($model->getErrors() as $errorGroup) {
                        foreach ($errorGroup as $error) {
                            echo "<b style='color:red'>" . $error . '</b><br/>';
                        }
                    }
                    ?>
                    <h3>Введите логин и пароль:</h3>
                </td>
            </tr>
            <tr>
                <td width="30%">Логин:</td>
                <td width="70%">
                    <?= Html::activeTextInput($model, 'username', ['id'=>'username']) ?>
                </td>
            </tr>
            <tr>
                <td width="30%">Пароль:</td>
                <td width="70%">
                    <?= Html::activePasswordInput($model, 'password') ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" align=right>
                    <input type=submit value='Войти' style="margin-right: 35px;">
                </td>
            </tr>
        </table>
    </div>
    <?= Html::endForm() ?>
</div>

<script>
    document.getElementById("username").focus();
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

