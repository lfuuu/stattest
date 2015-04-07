<form name="RTEDemo" action="?" method="post">
<input style='width:100%' type=hidden name=module value=clients>
<input style='width:100%' type=hidden name=action value=recontract>
<input style='width:100%' type=hidden name=contract_type value="{$contract.type}">
<input type=hidden name=id value='{$client.id}'>
{if $contract.type == "contract"}Договор {else}{if $contract.type == "agreement"}Дополнительное соглашение{else}Бланк заказа{/if}{/if} {if $contract.type != "blank"}&#8470;<input class=text style='width:100' type=text name=contract_no value="{if $contract.type == "contract"}{$contract.contract_no}{else}{$contract.contract_dop_no}{/if}">
от <input class="text contract_datepicker" style='width:100' type=text name=contract_date value="{if $contract.type == "contract"}{$contract.ts_date|mdate:"d.m.Y"}{else}{$contract.ts_dop|mdate:"d.m.Y"}{/if}">{/if}
<br>Комментарий <input class=text style='width:100' type=text name=comment value={$contract.comment}>

    <textarea id="text" name="contract_content" style="width: 100%; margin: 0px; height: 600px;">{$content}</textarea>

<script type="text/javascript">
{literal}
$(document).ready(function(){
    tinymce.init({
        selector: "textarea",
        plugins: [
            "advlist autolink lists link image charmap print preview anchor",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table contextmenu paste"
        ],
        toolbar: "insertfile undo redo | styleselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
    });
});

var datepicker_ru = {
           closeText: 'Закрыть',
           prevText: '&#x3c;Пред',
           nextText: 'След&#x3e;',
           currentText: 'Сегодня',
           monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь',
           'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
           monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн',
           'Июл','Авг','Сен','Окт','Ноя','Дек'],
           dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
           dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
           dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
           weekHeader: 'Не',
           dateFormat: 'dd.mm.yy',
           firstDay: 1,
           showMonthAfterYear: false,
           yearSuffix: ''};

    $('.contract_datepicker').datepicker(datepicker_ru);

{/literal}
</script>

<input type=submit value="Сохранить">
</form>
