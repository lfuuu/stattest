<?php
use app\classes\Html;
use app\assets\AppAsset;

/* @var $this app\classes\BaseView */
/* @var $content string */
AppAsset::register($this);

$version = $this->context->getVersion();
$isFormWithCode = \Yii::$app->is2fAuth();

if ($isFormWithCode) {
    $this->registerJsFile('views/site/login_with_code.js', [
        'depends' => [AppAsset::class,],
        'position' => \app\classes\BaseView::POS_FINALLY_END,
    ]);
}

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
                        <a href="/" class="logo-<?= \Yii::$app->isRus() ? 'ru' : 'eu'?>"></a>
                    </div>

                    <div class="alert alert-danger text-center error-block <?=$model->hasErrors() ? '' : 'ui-helper-hidden'?>" id="form-error">
                        <?php if ($model->hasErrors()) : ?>
                        <?php $errors = $model->getErrors(); ?>

                            <?php foreach ($errors as $errorGroup) : ?>
                                <?php foreach ($errorGroup as $error) : ?>
                                    <?= $error; ?><br />
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>


                    <?php
                    echo Html::beginForm('', '', ['class' => 'form-signin', 'id' => 'form-login']);

                    echo Html::activeHiddenInput(
                        $model, 'code_verification', [
                            'id' => 'code_verification',
                        ]
                    );

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
                            'id' => 'password',
                        ]
                    );

                    if ($isFormWithCode) {
                        echo Html::buttonInput(
                            'Войти', [
                                'class' => 'btn btn-primary btn-block',
                                'id' => 'login_btn',
                            ]
                        );
                    } else {
                        echo Html::submitButton(
                            'Войти', [
                                'class' => 'btn btn-primary btn-block',
                                'id' => 'login_btn',
                            ]
                        );
                    }

                    echo Html::endForm();
                    ?>
                <div class="text-center small text-muted">
                    App v.<?=$version ?>
                </div>
                </div>
            </div>
        </div>

        <div class="row ui-helper-hidden" id="row-loader">
            <div class="col-sm-6 col-md-4 col-md-offset-4">
                <div class="account-wall">
                    <div class="text-center small text-muted">
                        Отправляем запрос...
                    </div>
                </div>
            </div>
        </div>

        <div class="row ui-helper-hidden" id="row-code">
            <div class="col-sm-6 col-md-4 col-md-offset-4">
                <div class="account-wall" style="padding-top: 0">
                <div class="form-signin" style="padding-top: 1px;">
                        <h2 class="text-center"  style="margin-top: 20px;">Подтверждение входа</h2>
                        <div class="text-muted small" style="text-indent: 20px;padding-bottom: 5px;">Сейчас вам поступит входящий вызов с произвольного номера, сбросьте его. Затем введите последние четыре цифры номера в окно ниже.</div>
                        <input type="text" id="code" maxlength="4" class="form-control text-center" name=code" placeholder="Введите поледние 4 цифры вызова" autofocus="true" autocomplete="off">
                        <div>&nbsp;
                            <?php
                        echo Html::buttonInput(
                            'Войти', [
                                'class' => 'btn btn-primary btn-block',
                                'id' => 'login_with_code_btn',
                            ]
                        );
                        ?></div>
                    </div>
                </div>
            </div>
        </div>

    <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>

