<?php

use app\forms\transfer\ServiceTransferForm;

/** @var $model ServiceTransferForm */

$servicesGroups = $model->getServicesGroups();
$servicesObjects = $model->getServicesByIDs((array) $movedServices);
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
                        <?php
                        foreach ($movedServices as $serviceType => $services):
                            ?>
                            <b>
                                <?php
                                    echo (
                                        array_key_exists($serviceType, $servicesGroups)
                                            ? $servicesGroups[$serviceType]['title']
                                            : $serviceType
                                    );
                                ?>
                            </b><br />
                            <?php
                            for ($i=0, $s=sizeof($services); $i<$s; $i++):
                                if (!array_key_exists($services[$i], $servicesObjects))
                                    continue;

                                $service = $servicesObjects[ $services[$i] ]['object'];
                                $fulltext = '';

                                switch ($serviceType):
                                    case 'emails':
                                        $fulltext = $service->local_part . '@' . $service->domain;
                                        break;
                                    case 'usage_voip':
                                        $fulltext = $service->E164 . 'x' . $service->no_of_lines;
                                        break;
                                    case 'usage_ip_ports':
                                        $fulltext = $service->address;
                                        break;
                                    default:
                                        $tariff = $service->tariff;
                                        if ($tariff)
                                            $fulltext = $tariff->description;
                                        break;
                                endswitch;
                                ?>

                                <li>
                                    <?php echo $service->prev_usage_id;?>: <?php echo $fulltext; ?>
                                </li>
                                <?php
                            endfor;
                        endforeach;
                        ?>
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