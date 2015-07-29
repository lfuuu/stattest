<form>
    <table border="0" width="95%" align="center">
        <thead>
            <tr>
                <th>
                    <h2>Контрагент <?= $contragent->name; ?> успешно перемещен</h2>
                    <hr size="1" />
                </th>
            </tr>
        </thead>
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