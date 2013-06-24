<?xml version="1.0"?>
<statistics>
	<params>
		<routes>
			<route selected="{if (!$route)}1{else}0{/if}">
				<network></network>
				<actual_from></actual_from>
				<actual_to></actual_to>
			</route>
{foreach from=$routes_all item=item}
			<route selected="{if $route==$item[0]}1{else}0{/if}">
				<network>{$item[0]}</network>
				<actual_from>{$item[1]}</actual_from>
				<actual_to>{$item[2]}</actual_to>
			</route>
{/foreach}
		</routes>
		<from>
			<day>{$from_d}</day>
			<month>{$from_m}</month>
			<year>{$from_y}</year>
		</from>
		<to>
			<day>{$to_d}</day>
			<month>{$to_m}</month>
			<year>{$to_y}</year>
		</to>
		<detality>{$detality}</detality>
	</params>
	<lines>
{foreach from=$stats item=item key=key name=outer}{if $item.ip!="&nbsp;"}
		<line>
			<timestamp>{$item.ts}</timestamp>
			<bytes_in>{$item.in_bytes}</bytes_in>
			<bytes_out>{$item.out_bytes}</bytes_out>
{if $detality=='ip'}
			<ip>{$item.ip}</ip>
{/if}
		</line>
{/if}{/foreach}
	</lines>
	<total>
		<bytes_in>{$item.in_bytes}</bytes_in>
		<bytes_out>{$item.out_bytes}</bytes_out>
{if $detality=='ip'}
		<ip>{$item.ip}</ip>
{/if}
	</total>
	
</statistics>