<H3>СЕТЬ</H3>
<SPAN>
<TABLE class=price cellSpacing=4 cellPadding=2  border=0>

{foreach from=$net item=field key=key}
 <tr bgcolor={cycle values="#eeeeee,#d0d0d0"}>
 	<td>{$key}</td>
 	<td>
 	{if $priv.edit eq 'true'} 
 	<input type="text" name="{$key}" value="{$field}" size="20">
 	{else}
 		{$field}
 	{/if}
 	</td>
 </tr>	
{/foreach}
{if $priv.edit eq 'true'} 
 <tr bgcolor={cycle values="#eeeeee,#d0d0d0"}>
 	<td></td>
 	<td>

 	<input type="submit" value="Обновить" size="20">
 	
 	</td>
 </tr>	
 {/if}
</table>
</SPAN>

{if $priv.activate}
<SPAN>
	<a href="#">Активировать подключение</a>
	- задать дату подключения <br> 
	- проверить что задана уникальная сеть <br>
</span>
{/if}

{if $priv.deactivate}
<SPAN>
	<a href="#">Временно заблокировать подключение</a>
	- если ходит с помощью ppp логинов то заблокировать <br> 
	- если индивидуальшик создать трабл для АНдрея и Антона <br>
	
</span>
{/if}

{if $priv.close}
<SPAN>
	<a href="#">Отключить подключение</a>
	- написать заявку на отключение если индивидуальщик <br>
	- написать заявку  забрать оборудование <br>
	
	
</span>
{/if}

{if $priv.changenet}
<SPAN>
	<a href="#">Сменить сеть </a>
	- отключить старую сеть <br>
	- создать новую сеть  <br>
	
	
</span>
{/if}
