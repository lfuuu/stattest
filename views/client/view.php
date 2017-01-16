<?php

use app\assets\AppAsset;
use app\classes\uu\model\ServiceType;

$this->registerJsFile('@web/js/behaviors/immediately-print.js', ['depends' => [AppAsset::className()]]);
?>

<div class="row">
    <div class="col-sm-12">
        <?= $this->render('block/main', ['client' => $client, 'account' => $account, 'services' => $services]); ?>
        <div class="row">
            <div class="col-sm-10">
                <?= $this->render('block/status', ['account' => $account, 'contractForm' => $contractForm]); ?>
                <?= $this->render('block/contact', ['account' => $account]); ?>
            </div>
            <div class="col-sm-2">
                <?= $this->render('block/rightmenu', ['account' => $account]); ?>
            </div>
        </div>
        <?= $this->render('block/trouble', ['troubles' => $troubles, 'serverTroubles' => $serverTroubles]); ?>
        <?= $this->render('block/service', ['account' => $account, 'services' => $services, 'account' => $account]); ?>
        <?php
            if ($uuFilterModel) {
                echo $this->render('//uu/account-tariff/_indexVoip',
                    [
                        'filterModel' => $uuFilterModel,
                        'isShowAddButton' => false,
                        'packageServiceTypeIds' => [ServiceType::ID_VOIP_PACKAGE],
                    ]
                );
            }
        ?>

    </div>

    <?= $this->render('block/style'); ?>

    <script type="text/javascript">
        $('.showFullTable').on('click', function () {
            $('.fullTable').toggleClass('collapse');
        });
        $(document).ready(function () {
            $('.select2').select2();
        });
    </script>
</div>

