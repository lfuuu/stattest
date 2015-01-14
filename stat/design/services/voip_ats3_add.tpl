<TR>
    <TD align=center colspan=2>
    <input type=hidden name="voip_ats3_add" id="voip_ats3_add" value="1">
    <br><b>Настройки новой телефонии</b> (заведение номера)(доп поля)</td>
</TR>
<TR>
    <TD class=left width=40%>Тип подключения</td>
    <TD class=right width=60%>
        <select name="type_connect" id="type_connect">
            <option value="line">Телефонный номер</option>
{if $form_ats3.vpbxs}<option value="vpbx">Завести на ВАТС</option>{/if}
{if $form_ats3.multis}<option value="multi">Мультитранк</option>{/if}
        </select></td>
</TR>

{if $form_ats3.vpbxs}
<TR id="tr_vpbx" style="display: none;">
    <TD class=left width=40%>ВАТС:</td>
    <TD class=right width=60%>
        <select name="vpbx_id" id="vpbx_id">
            <option value=0>-- Не выбранно --</option>
            {html_options options=$form_ats3.vpbxs}
        </select></td>
</TR>
{/if}

{if $form_ats3.multis}
<TR id="tr_multi" style="display: none;">
    <TD class=left width=40%>Мультитранк:</td>
    <TD class=right width=60%>
        <select name="multitrunk_id" id="multitrunk_id">
            <option value=0>-- Не выбранно --</option>
            {html_options options=$form_ats3.multis}
        </select></td>
</TR>
{/if}

<TR id="tr_sip_accounts">
    <TD class=left width=40%>Кол-во SIP учеток:</td>
    <TD class=right width=60%>
        <select name="sip_accounts" id="sip_accounts">
            <option value="0">не создавать</option>
            <option value="1">1 учетка</option>
            <option value="auto">по кол-ву линий</option>
        </select></td>
</TR>
<script>
{literal}
$(function(){
    $("#type_connect").change(function(ev){
        var typeConnect = $(this).val();

        var trVpbx = $("#tr_vpbx");
        var trMulti = $("#tr_multi");

        if (typeConnect == 'vpbx')
        {
            trVpbx.show();
        } else {
            trVpbx.hide();
        }

        if (typeConnect == 'multi')
        {
            trMulti.show();
        } else {
            trMulti.hide();
        }
    });

})

    function checkVoipAts3Add()
    {
        var typeConnect = $("#type_connect").val();

        if (typeConnect == "vpbx")
        {
            var vpbxId = $("#vpbx_id").val();

            if (vpbxId == false || vpbxId == 0)
            {
                alert("ВАТС не выбран");
                return false;
            }
        }

        if (typeConnect == "multi")
        {
            var multiId = $("#multitrunk_id").val();

            if (multiId == false || multiId == 0)
            {
                alert("Мультитранк не выбран");
                return false;
            }
        }

        return true;
    }
{/literal}
</script>
