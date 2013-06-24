Можно скопировать некоторые поля отсюда:<br>
<SELECT onchange="eval(this.value);">{foreach from=$dbform_f_ref key=key item=item}
<option value='dbform.price.value="{$item.price}"; document.getElementById("tr_price").childNodes[1].innerHTML="{$item.price}"; dbform.description.value="{$item.description}"; dbform.period.value="{$item.period}"; document.getElementById("tr_period").childNodes[1].innerHTML="{$item.period}"; {if $item.period=="once"}dbform.actual_to.value=dbform.actual_from.value;{else}dbform.actual_to.value="2029-01-01";{/if}'>{$item.description} | {$item.price}</option>
{/foreach}</SELECT><br>
<br>
<h3>История изменений</h3>
{foreach from=$dbform_f_log key=key item=T}
<b>{$T.ts} - {$T.user}</b>: {$T.description} / {$T.amount} / {$T.price}<br>
{/foreach}