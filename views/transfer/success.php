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
                        <b>Успешно перенесены услуги:</b>
                    </p>

                    <ul style="height: 350px;">
                        <?php foreach ($movedServices as $serviceClass => $serviceIds): ?>
                            <b><?= (new $serviceClass)->getTitle(); ?></b><br />
                            <?php foreach($serviceIds as $serviceId): ?>
                                <?php
                                    $service = $model->getService($serviceClass, $serviceId);

                                    if ($service instanceof \app\models\Emails) {
                                        $fulltext = $service->local_part . '@' . $service->domain;
                                    } elseif ($service instanceof \app\models\UsageVoip) {
                                        $fulltext = $service->E164 . 'x' . $service->no_of_lines;
                                    } elseif ($service instanceof \app\models\UsageIpPorts) {
                                        $fulltext = $service->address;
                                    } else {
                                        $fulltext = $service->tariff ? $service->tariff->description : '';
                                    }

                                ?>
                                <li>
                                    <?= $service->prev_usage_id;?>: <?= $fulltext; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </ul>
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