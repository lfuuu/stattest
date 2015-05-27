<form method="POST" action="/transfer/process">
    <input type="hidden" name="transfer[current_id]" value="<?php echo $client->id; ?>" />
    <table border="0" width="95%" align="center">
        <col width="35%" />
        <col width="40%" />
        <col width="15%" />
        <thead>
        <tr>
            <th colspan="3">
                <h2>Лицевой счет № <?php echo $client->id; ?> <?php echo $client->firma; ?></h2>
                <hr size="1" />
            </th>
        </tr>
        <tr>
            <th>Перенести</th>
            <th>на лицевой счет</th>
            <th>дата переноса</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td valign="top">
                <input type="radio" name="transfer[services]" value="all" checked="checked" data-action="services-choose" />&nbsp;Все<br />
                <input type="radio" name="transfer[services]" value="custom" data-action="services-choose" />&nbsp;Выбранные услуги

                <div style="width: 80%; display: none;">
                    <?php
                    foreach ($services as $service_type => $records):
                        $title = $field = '';
                        switch ($service_type)
                        {
                            case 'usage_voip':
                                $field = 'name';
                                break;
                            default:
                                $field = 'description';
                                break;
                        }
                        for ($i=0, $s=sizeof($records); $i<$s; $i++):
                            $tariff = $records[$i]->getCurrentTariff();
                            $text = $fulltext = $tariff->{$field};
                            if (mb_strlen($text, 'UTF-8') > 24)
                                $text = mb_substr($text, 0, 24, 'UTF-8') . '...';
                            ?>
                            <input type="checkbox" name="transfer[custom][]" value="<?php echo $records[$i]->id; ?>" />
                            &nbsp;<abbr title="<?php echo $fulltext; ?>"><?php echo $text; ?></abbr><br />
                            <?php
                        endfor;
                    endforeach;
                    ?>
                </div>

            </td>
            <td valign="top">
                <?php
                $firstRow = true;
                foreach ($accounts as $account):
                    ?>
                    <input type="radio" name="transfer[account]" value="<?php echo $account->id; ?>" data-action="account-choose"<?php echo ($firstRow ? 'checked="checked"' : ''); ?> />&nbsp;№ <?php echo $account->id; ?> - <?php echo $account->firma; ?><br />
                    <?php
                    $firstRow = false;
                endforeach;
                ?>
                <input type="radio" name="transfer[account]" value="custom" data-action="account-choose" />&nbsp;Другой клиент
                <div class="account-search" style="display: none;">
                    <input type="text" name="transfer[account_custom]" class="text" value="" />
                    <div class="text" id="search-results" style="position:absolute; floating:true; padding:5 5 5 5; width:410px; height:200px; overflow-y:scroll; overflow-x:hidden"></div>
                </div>
            </td>
            <td valign="top">
                <?php
                $firstRow = true;
                foreach ($dates as $date):
                    ?>
                    <input type="radio" name="transfer[actual_from]" value="<?php echo $date; ?>" data-action="date-choose"<?php echo ($firstRow ? 'checked="checked"' : ''); ?> />&nbsp;<?php echo date('d.m.Y', strtotime($date)); ?><br />
                    <?php
                    $firstRow = false;
                endforeach;
                ?>
                <input type="radio" name="transfer[actual_from]" value="custom" data-action="date-choose" />&nbsp;Другая дата<br />
                <input type="text" name="transfer[actual_custom]" class="text" style="visibility: hidden;" />
            </td>
        </tr>
        </tbody>
    </table>
</form>