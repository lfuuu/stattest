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
                        <?php foreach ($movedServices as $serviceType => $serviceIds): ?>
                            <div>
                                <?php
                                $services = [];
                                foreach($serviceIds as $serviceId)
                                        $services[] = $model->getService($serviceType, $serviceId);
                                ?>
                                <b><?= $services[0]::getTransferHelper()->getTypeTitle(); ?></b>
                                <ul>
                                    <?php foreach($services as $service): ?>
                                        <?php
                                        list($fulltext, $description, $checkboxOptions) = (array) $service::getTransferHelper($service)->getTypeDescription();

                                        if (mb_strlen($fulltext, 'UTF-8') > 30):
                                            $text = mb_substr($fulltext, 0, 30, 'UTF-8') . '...';
                                        else:
                                            $text = $fulltext;
                                        endif;
                                        ?>
                                        <li>
                                            <?= $service->prev_usage_id; ?>: <abbr title="<?= $service->prev_usage_id . ': ' . $fulltext; ?>"><?= $text; ?></abbr>
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