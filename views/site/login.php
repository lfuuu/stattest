<?php
use app\classes\Html;
use app\assets\AppAsset;

/* @var $this app\classes\BaseView */
/* @var $content string */
AppAsset::register($this);

$version = $this->context->getVersion();
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

    <div class="container">
        <div class="row">
            <div class="col-sm-6 col-md-4 col-md-offset-4">
                <div class="account-wall">
                    <div class="text-center site_caption">
                        <a href="/" class="logo-<?=\Yii::$app->isRus() ? 'ru' : 'eu'?>"></a>
                    </div>

                    <?php
                    if ($model->hasErrors()) : ?>
                        <?php $errors = $model->getErrors(); ?>
                        <div class="alert alert-danger text-center error-block">
                            <?php foreach ($errors as $errorGroup) : ?>
                                <?php foreach ($errorGroup as $error) : ?>
                                    <?= $error; ?><br />
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php
                    echo Html::beginForm('', '', ['class' => 'form-signin']);

                    echo Html::activeTextInput(
                        $model, 'username', [
                            'id' => 'username',
                            'class' => 'form-control',
                            'placeholder' => 'Логин',
                            'autofocus' => 'true',
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
                <div class="text-center">
                    <div class="message">Application version: <?=$version ?></div>
                </div>
                </div>
            </div>
        </div>
    </div>

    <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>

