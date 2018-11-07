<?php
/**
 * @var \app\classes\BaseView $this
 * @var ClientSuper $client
 * @var ClientAccount $account
 * @var ContractEditForm $contractForm
 * @var Trouble[] $troubles
 * @var Trouble[] $serverTroubles
 * @var ActiveRecord[] $services
 * @var AccountTariffFilter $uuFilterModel
 * @var ClientContact[] $contacts
 */

use app\assets\AppAsset;
use app\forms\client\ContractEditForm;
use app\models\ClientAccount;
use app\modules\uu\filter\AccountTariffFilter;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$this->registerJsFile('@web/js/behaviors/immediately-print.js', ['depends' => [AppAsset::class]]);
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => $this->title = 'Аккаунт', 'url' => Url::to(['client/view', 'id' => $account->id])],
    ],
]) ?>

<?= $this->render('block/main', ['client' => $client, 'account' => $account, 'services' => $services]); ?>

<div class="row">
    <div class="col-sm-10">
        <?= $this->render('block/status', ['account' => $account, 'contractForm' => $contractForm]); ?>
        <?= $this->render('block/contactView', ['account' => $account, 'contacts' => $contacts]); ?>
    </div>
    <div class="col-sm-2">
        <?= $this->render('block/rightmenu', ['account' => $account]); ?>
    </div>
</div>

<?= $this->render('block/trouble', ['troubles' => $troubles, 'serverTroubles' => $serverTroubles]); ?>

<?= $this->render('block/service', ['account' => $account, 'services' => $services]); ?>

<div class="row">

    <?php if ($uuFilterModel) : ?>
        <h2><a href="<?= Url::to(['/uu/account-tariff', 'AccountTariffFilter[client_account_id]' => $account->id]) ?>">Универсальные услуги</a></h2>
        <?= $this->render('/../modules/uu/views/account-tariff/_indexVoipLight', ['filterModel' => $uuFilterModel]) ?>
    <?php endif ?>

    <?= $this->render('/../modules/sim/views/card/_listByClient', ['client_account_id' => $account->id]); ?>
</div>

<?= $this->render('block/style'); ?>


