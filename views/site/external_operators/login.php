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
        <style type="text/css">
        .form-signin
        {
            max-width: 330px;
            padding: 15px;
            margin: 0 auto;
        }
        .form-signin input[type="text"]
        {
            margin-bottom: -1px;
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
        }
        .form-signin input[type="password"]
        {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
        .account-wall
        {
            margin-top: 20px;
            padding: 20px 0px 20px 0px;
            background-color: #F7F7F7;
            -moz-box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.3);
            -webkit-box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.3);
            box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.3);
        }
        </style>
        <?php $this->head() ?>
    </head>
    <body>
        <?php $this->beginBody() ?>

        <div class="container">
            <div class="row">
                <div class="col-sm-6 col-md-4 col-md-offset-4">
                    <div class="account-wall">
                        <div style="text-align: center">
                            <div class="site_caption" style="float: none; display: inline">
                                <a href="//mcn.ru" class="logo"></a>
                                <div class="message">Сервер статистики</div>
                            </div>
                        </div>

                        <?php
                        if ($model->hasErrors()): ?>
                            <?php $errors = $model->getErrors(); ?>
                            <div class="alert alert-danger" style="text-align: center; font-weight: bold; width: 300px; margin: 15px auto 0;">
                                <?php if (count($errors) == 2): ?>
                                    Заполните все поля
                                <?php else: ?>
                                    <?php foreach ($errors as $errorGroup): ?>
                                        <?php foreach ($errorGroup as $error): ?>
                                            <?= $error; ?>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php
                        echo Html::beginForm('', '', ['class' => 'form-signin']);

                        echo Html::activeTextInput(
                            $model, 'username', [
                                'id' => 'username',
                                'class' => 'form-control',
                                'placeholder' => 'Логин',
                            ]
                        );
                        echo Html::activePasswordInput(
                            $model, 'password', [
                                'class' => 'form-control',
                                'placeholder' => 'Пароль',
                            ]
                        );
                        echo Html::submitButton(
                            'Войти', [
                                'class' => 'btn btn-primary btn-block'
                            ]
                        );

                        echo Html::endForm();
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function() {
            $('#username').trigger('focus');
        });
        </script>

        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>