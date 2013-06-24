<?xml version="1.0"?>
<statistics>
	<params>
		<phones>
			<phone selected="{if (!$phone)}1{else}0{/if}">
				<id></id>
				<name></name>
			</phone>
{foreach from=$phones_all item=item}
			<phone selected="{if $phone==$item.id}1{else}0{/if}">
				<id>{$item.id}</id>
				<number>{$item.E164}</number>
			</phone>
{/foreach}
		</phones>
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
		<paidonly>{$paidonly}</paidonly>
	</params>
	<lines>
{foreach from=$stats item=item key=key name=outer}{if $item.num_to!="&nbsp;"}
		<line>
			<timestamp>{$item.ts1}</timestamp>
			<duration>{$item.ts2}</duration>
			<price>{$item.price}</price>
{if $detality=='call'}
			<caller>{$item.num_from}</caller>
			<called>{$item.num_to}</called>
{/if}
		</line>
{/if}{/foreach}
	</lines>
	<total>
		<duration>{$item.ts2}</duration>
		<price>{$item.price}</price>
	</total>
</statistics>
