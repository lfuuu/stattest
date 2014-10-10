<table class="price" align='center' {*class='timetable'*} cellspacing=0 border=1>{foreach from=$e164 item='num' name='outer'}
<tr class="{if $smarty.foreach.outer.iteration%2==count($tt_trouble.stages)%2}even{else}odd{/if}" style="margin-bottom:2px;" align='center'>
	<td>{$num.e164}</td>
	<td><a href='?module=clients&id={$num.id}' target='_blank'>{$num.client}</a></td>
</tr>
{/foreach}</table>