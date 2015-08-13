<?php

use app\forms\transfer\ServiceTransferForm;

/** @var $model ServiceTransferForm */
?>
<form>
    <table border="0" width="95%" align="center">
        <thead>
            <tr>
                <th>
                    <h2>Лицевой счет № <?php echo $clientAccount->id; ?> <?php echo $clientAccount->contragent->name; ?></h2>
                    <hr size="1" />
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td valign="top" style="overflow-y: auto; overflow-x: hidden;">
                    <p>
                        <b>Успешно перенесены услуги / устройства:</b>
                    </p>

                    <div style="height: 350px;">
                        <?php foreach ($movedServices as $serviceType => $serviceIds):
                            $serviceTitle = '';
                            ?>
                            <div>
                                <b><?= $serviceIds[0]->$service->getTypeTitle() ?></b>
                                <ul>
                                    <?php foreach($serviceIds as $serviceId):
                                        $service = $model->getService($serviceType, $serviceId);
                                        ?>
                                        <li>
                                            <?= $service->prev_usage_id; ?>: <?= $service->getTypeDescription(); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                        <?php endforeach; ?>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <div style="position: fixed; bottom: 0; right: 15px;">
        <button type="button" id="dialog-close" style="width: 100px;" class="btn btn-primary">Закрыть</button>
    </div>
</form>

<script type="text/javascript">
jQuery(document).ready(function() {
    $('#dialog-close').click(function() {
        window.parent.location.reload(true);
        window.parent.$dialog.dialog('close');
    });
    $(document).bind('keydown', function(e) {
        if (e.keyCode === $.ui.keyCode.ESCAPE)
            $('#dialog-close').trigger('click');
    });
});
</script>