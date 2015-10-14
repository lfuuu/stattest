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
        <nav class="navbar navbar-default navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <a class="logo" style="margin: 4px 30px 0px 0px" href="//mcn.ru" target="_blank"></a>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <div style="text-align: center; font-weight: bold; font-size: 14px;">
                        <?= Yii::$app->controller->operatorTitle; ?>
                    </div>
                    <ul class="nav navbar-nav">
                        <li<?= (Yii::$app->controller->menuItem == 'indexReport' ? ' class="active"' : ''); ?>>
                            <a href="/">Заказы</a>
                        </li>
                        <li<?= (Yii::$app->controller->menuItem == 'createRequest' ? ' class="active"' : ''); ?>>
                            <a href="/site/create-request">Создание заявки</a>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right" style="margin-right: 0px;">
                        <li>
                            <div style="margin: 15px 20px;">
                                <?= Yii::$app->user->identity->name; ?>
                            </div>
                        </li>
                        <li>
                            <div style="margin-top: 5px;">
                                <a href="/site/logout/" class="btn btn-primary" style="height: 35px;">Выход</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container">
            <div style="padding-top: 60px;">
                <?= $content; ?>
            </div>

        </div>

        <?php $this->endBody() ?>

    </body>
</html>
<?php $this->endPage() ?>