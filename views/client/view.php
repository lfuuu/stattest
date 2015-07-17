<div class="row" style="margin-right: 0;">
    <div class="col-sm-12">
        <?= $this->render('block/main', ['client' => $client, 'account' => $account]); ?>
        <div class="row">
            <div class="col-sm-10">
                <?= $this->render('block/status', ['account' => $account, 'contractForm' => $contractForm]); ?>
                <?= $this->render('block/contact', ['account' => $account]); ?>
                <?= $this->render('block/file', ['account' => $account]); ?>
            </div>
            <div class="col-sm-2">
                <?= $this->render('block/rightmenu', ['account' => $account]); ?>
            </div>
        </div>
        <?= $this->render('block/trouble', ['troubles' => $troubles]); ?>
        <?= $this->render('block/service', ['services' => $services]); ?>

    </div>

    <?= $this->render('block/style'); ?>


    <script>
        d = false;
        $('.showFullTable').on('click', function () {
            $(this).parent().find('.fullTable').toggle();
        });
        $(document).ready(function () {
            $('.select2').select2();
        });
    </script>
</div>

