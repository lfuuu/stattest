<?php

use app\classes\Assert;
use app\models\Emails;
use app\models\UsageExtra;
use app\models\UsageIpPorts;
use app\models\UsageSms;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\models\UsageWelltime;

$servicesTitle = [
    'usage_extra' => 'Доп. услуги',
    'usage_sms' => 'SMS',
    'usage_welltime' => 'Welltime',
    'usage_voip' => 'Телефония номера',
    'usage_trunk' => 'Телефония транки',
    'emails' => 'E-mail',
    'usage_ip_ports' => 'Интернет'
];
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
                            <b><?php echo (array_key_exists($serviceType, $servicesTitle) ? $servicesTitle[$serviceType] : $service_type); ?></b><br />
                            <?php
                            for ($i=0, $s=sizeof($services); $i<$s; $i++):
                                $fulltext = '';

                                switch ($serviceType):
                                    case 'emails':
                                        $service = Emails::findOne($services[$i]['id']);
                                        Assert::isObject($service);
                                        $fulltext = $service->local_part . '@' . $service->domain;
                                        break;
                                    case 'usage_sms':
                                        $service = UsageSms::findOne($services[$i]['id']);
                                        Assert::isObject($service);
                                        break;
                                    case 'usage_extra':
                                        $service = UsageExtra::findOne($services[$i]['id']);
                                        Assert::isObject($service);
                                        break;
                                    case 'usage_ip_ports':
                                        $service = UsageIpPorts::findOne($services[$i]['id']);
                                        Assert::isObject($service);
                                        $fulltext = $service->address;
                                        break;
                                        break;
                                    case 'usage_welltime':
                                        $service = UsageWelltime::findOne($services[$i]['id']);
                                        Assert::isObject($service);
                                        break;
                                    case 'usage_voip':
                                        $service = UsageVoip::findOne($services[$i]['id']);
                                        Assert::isObject($service);
                                        $fulltext = $service->E164 . 'x' . $service->no_of_lines;
                                        break;
                                endswitch;

                                if (empty($fulltext)):
                                    $tariff = $service->tariff;
                                    if ($tariff)
                                        $fulltext = $tariff->description;
                                endif;
                                ?>

                                <li>
                                    <?php echo $service->id;?>: <?php echo $fulltext; ?>
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