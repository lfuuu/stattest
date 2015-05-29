<div style="text-align: center;">
    <form>
        <h2>
            Услуги успешно перенесны.
        </h2>
        <button type="button" id="dialog-close" class="btn btn-link">Закрыть</button>
        <button type="button" id="redirect-to" class="btn btn-primary" data-account-id="<?php echo $target_account_id; ?>">Перейти к лицевому счету</button>
    </form>
</div>

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