	<form>
	<input type='hidden' name='module' value='services' />
	<input type='hidden' name='action' value='e164' />
	<input type='hidden' name='sub' value='show' />
	<table align='center' border='0'>
		<tr><td></td><td></td><td></td><td></td><td style='padding:0px 0px 0px 0px'><ul style='margin:0px 0px 0px 0px;padding:0px 0px 0px 20px'>
			<li><b>%</b> - Много символов</li>
			<li><b>_</b> - Один символ</li>
		</td></tr>
		<tr>
			<td valign="top">
				<select name='free_or_non[]' multiple size='2'>
					<option value='free' {if isset($fon) && in_array('free',$fon)}selected{/if}>Свободные</option>
					<option value='nonfree' {if isset($fon) && in_array('nonfree',$fon)}selected{/if}>Используемые</option>
				</select>
			</td>
			<td valign="top">
				<select name='pref[]' multiple size='{$prefs_len}'>
					{foreach from=$prefs item='pref'}<option value='{$pref}' {if isset($pref_) && in_array($pref,$pref_)}selected{/if}>{$pref}</option>{/foreach}
				</select>
			</td>
			<td valign="top">
				<select name='is[]' multiple size='7'>
					<option value='just' {if isset($is) && in_array('just',$is)}selected{/if}>Обычные</option>
					<option value='special1' {if isset($is) && in_array('special1',$is)}selected{/if}>Красивые Платина</option>
                    <option value='special2' {if isset($is) && in_array('special2',$is)}selected{/if}>Красивые Золото</option>
                    <option value='special3' {if isset($is) && in_array('special3',$is)}selected{/if}>Красивые Серебро</option>
                    <option value='special4' {if isset($is) && in_array('special4',$is)}selected{/if}>Красивые Бронза</option>
				</select>
			</td>
			<td valign="top"><select name='is_our' size='2'>
				<option value='alien' {if $is_our=='alien'}selected{/if}>Арендуемые</option>
                <option value='reserve' {if $is_our=='reserve'}selected{/if}>Зарезервированные</option>
                <option value='our' {if $is_our=='our'}selected{/if}>Собственные</option>
			</select></td>
		</tr>
		<tr style='text-align:center'><td colspan='5'><input type='submit' value='Ok' /></td></tr>
	</table>
	</form>


{if isset($sub) && $sub eq 'show'}
<table align='center' width='60%' border=1><tr align='center'>
	<td><b>Свободные</b></td>
	<td><b>Зарезервированные</b></td>
    <td><b>Собственные</b></td>
    <td><b>Используемые</b></td>
</tr>
<tr><td align='center' colspan="3">Количество: {$free_count}</td><td align='center'>Количество: {$nonfree_count}</td></tr>
	<tr valign='top' align='center'>
	<td><table>
		{foreach from=$free_nums item='num'}
            {if $num.client_id == ''}
			<tr><td >
				<a {if $num.to_add == "N"}style="color: gray;"{else}style='color:{if $num.beauty_level > 0}blue{else}black{/if}{/if}' href='/usage/number/view?did={$num.number}'>{$num.number}</a>
                {if $num.actual_to} (откл: {$num.actual_to}){/if}
            </td></tr>
            {/if}
		{/foreach}
	</table></td>
    <td valign="top"><table>
        {foreach from=$free_nums item='num'}
            {if $num.client_id != '' and $num.client_id != '764' and $num.usage_id == ''}
            <tr><td>
                <a {if $num.to_add == "N"}style="color: gray;"{else}style='color:{if $num.beauty_level > 0}blue{else}black{/if}{/if}' href='/usage/number/view?did={$num.number}'>{$num.number}</a>
                (клиент: <a style='color:{if $num.beauty_level > 0}blue{else}black{/if}' href='/client/view?id={$num.client_id}'>{$num.client_id}</a>
                резерв: {$num.reserved_free_date|substr:0:10})
            </td></tr>
            {/if}
        {/foreach}
    </table></td>
	<td valign="top"><table>
        {foreach from=$free_nums item='num'}
            {if $num.client_id == '764' and $num.usage_id == ''}
            <tr><td >
                <a style='color:{if $num.beauty_level > 0}blue{else}black{/if}' href='/usage/number/view?did={$num.number}'>{$num.number}</a>
            </td></tr>
            {/if}
        {/foreach}
    </table></td>
	<td valign="top"><table>
		{foreach from=$nonfree_nums item='num'}
			<tr><td>
				<a style='color:{if $num.beauty_level > 0}blue{else}black{/if}' href='/usage/number/view?did={$num.e164}'>{$num.e164}</a>
			</td></tr>
		{/foreach}
	</table></td>
</tr></table>
{/if}
