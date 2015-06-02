<form>
    <table border="0" width="95%" align="center">
        <thead>
            <tr>
                <th>
                    <h2>Лицевой счет № <?php echo $client->id; ?> <?php echo $client->firma; ?></h2>
                    <hr size="1" />
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td valign="top" align="center">
                    <h2 style="margin-top: 0px;">
                        Услуги успешно перенесны.
                    </h2>
                    <button type="button" id="dialog-close" class="btn btn-link">Закрыть</button>
                    <button type="button" id="redirect-to" class="btn btn-primary" data-account-id="<?php echo $target_account_id; ?>">Перейти к лицевому счету</button>
                </td>
            </tr>
        </tbody>
    </table>
</form>

<script type="text/javascript">
jQuery(document).ready(function() {
    $('#dialog-close').click(function() {
        window.parent.$dialog.dialog('close');
    });
    $('#redirect-to').click(function(e) {
        e.preventDefault();
        self.location.href = '/?module=clients&id=' + $(this).data('account-id');
    });
});
</script>