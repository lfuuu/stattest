<?php
use app\assets\AppAsset;
use app\assets\LayoutMainAsset;
use app\classes\Html;
use app\models\ClientAccount;
use yii\helpers\Url;

/** @var app\classes\BaseView $this */
/** @var \app\models\User$user */
/** @var string $content  */

global $fixclient_data;

AppAsset::register($this);
LayoutMainAsset::register($this);

$user = Yii::$app->user->identity;
$myTroublesCount = $this->context->getMyTroublesCount();
$version = $this->context->getVersion();

/** @var ClientAccount|null $activeClient */
$activeClient = isset($fixclient_data['id']) ?
    ClientAccount::findOne($fixclient_data['id']) :
    null;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <base href="/"/>
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>

        <?php $this->beginBody() ?>

        <div class="row panel-top">
            <div class="col-sm-12">
                <div class="row pull-left block-left">
                    <div class="col-sm-6">
                        <a href="/" class="logo"></a>
                    </div>
                    <div class="col-sm-6">
                        <?= $this->render('widgets/logged-user', [
                            'user' => $user,
                            'troublesCount' => $myTroublesCount,
                        ]) ?>
                    </div>
                </div>

                <div id="top_search" class="block-search">
                    <?php if (Yii::$app->user->can('clients.read')) : ?>
                        <div class="row">
                            <?= $this->render('widgets/search') ?>

                            <?php if ($activeClient) : ?>
                                <div class="col-sm-12">
                                    <?php
                                    $str = htmlspecialchars($activeClient->contract->contragent->name . ' / Договор
                                    № ' . $activeClient->contract->number . ' / ' . $activeClient->getAccountTypeAndId());
                                    ?>
                                    <h2 class="c-blue-color" title="<?= $str ?>">
                                        <a href="<?= Url::toRoute(['/client/view', 'id' => $activeClient->id, '#' => 'contragent' . $activeClient->contract->contragent_id ]) ?>">
                                            <?= $activeClient->getName() ?>
                                        </a>
                                    </h2>
                                    <a href="/account/unfix" title="Снять" class="search-unset"><i class="uncheck"></i></a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <?php
        $isHideLeftLayout = Yii::$app->session->get('isHideLeftLayout', false);
        ?>

        <div class="layout_left col-sm-2" <?= $isHideLeftLayout ? 'style="left: -350px;"' : '' ?>>

            <?= $this->render('widgets/left_menu', ['user' => $user]); ?>

            <div class="block-empty"></div>
        </div>

        <div class="layout_main <?= $isHideLeftLayout ? 'col-sm-12' : 'col-sm-10 col-md-push-2' ?>">

            <?php // скрыть/показать левое меню ?>
            <button type="button" class="btn btn-info btn-xs panel-toggle-button <?= $isHideLeftLayout ? '' : 'active' ?>"><?= $isHideLeftLayout ? '›' : '‹' ?></button>

            <?= $this->render('widgets/messages') ?>

            <div class="layout-content">
                <?= $content ?>

                <div class="row">
                    <div class="col-sm-12 copyright">
                        <a href="http://www.mcn.ru/">
                            <img height="16" src="/images/logo_msn_s.gif" width="58" border="0" />
                        </a><br />
                        <span>
                            ©<?=date('Y')?> MCN. тел. (495) 105–9999 (отдел продаж),
                            (495) 105–9995 (техподдержка).
                            Версия: <?=$version ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Это фикс бага с select2 v4. Он вызывается раньше инициализации, надо его вызывать позже.
        // Правильнее это сделать в vendor/kartik-v/yii2-krajee-base/WidgetTrait.php::getPluginScript, но vendor менять не могу
        // Вторая проблема - datepicker делает trigger('change'), а grid Подписан на onchange
        // Мне стыдно за такой говнокод, но по-другому исправить не получается.
        if ($this->js) {
            foreach ($this->js as &$scripts) {
                foreach ($scripts as &$script) {
                    $script = preg_replace('/jQuery\.when\((.*?)\)\.done/', 'jQuery.when(  setTimeout(function(){$1},10)  ).done', $script);
                    $script = preg_replace('/jQuery\([^\)]+?\)\.yiiGridView\([^\)]+?\);/', 'setTimeout(function(){$0},100);', $script);
                }

                unset($script);
            }

            unset($scripts);
        }
        ?>

        <?php $this->endBody() ?>

        <?php
        // Вывести необходимые скрипты для работы с сокетами
        \app\modules\socket\classes\Socket::me()->echoScript();
        ?>

    </body>
    <div id="alf_form" style="width: 470px; height: 206px; background-color: #e5e5e5 ; display: none;" class="well">
        <label>##Создать ссылку на описание:</label><br>
        <span>URL: <br><input type="text" id="alf_url" style="width: 400px;"></span>
        <div style="vertical-align: top;">Описание: <br><textarea id="alf_text" style="width: 400px; height: 75px;">###</textarea></div>
        <span><button class="btn btn-primary btn-xs alf_save">##Создать ссылку</button></span>
        <span><button class="btn btn-warning btn-xs alf_delete">Удалить</button></span>
        <span><button class="btn btn-cancel btn-xs alf_cancel">Отмена</button></span>
    </div>

</html>
<?php $this->endPage() ?>
