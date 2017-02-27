<?php
/**
 * @var \yii\web\View $this
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
use app\classes\uu\filter\AccountTariffFilter;
use app\classes\uu\model\ServiceType;
use app\forms\client\ContractEditForm;
use app\models\ClientAccount;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$this->registerJsFile('@web/js/behaviors/immediately-print.js', ['depends' => [AppAsset::className()]]);
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => $this->title = 'Аккаунт', 'url' => Url::to(['client/view', 'id' => $account->id])],
    ],
]) ?>

<div class="row">
    <div class="col-sm-12">
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
        <?php
        if ($uuFilterModel) {
            echo $this->render('//uu/account-tariff/_indexVoip',
                [
                    'filterModel' => $uuFilterModel,
                ]
            );
        }
        ?>

    </div>

    <?= $this->render('block/style'); ?>

    <script type="text/javascript">
        $(document).ready(function () {
            $('.select2').select2();
        });
    </script>
</div>

