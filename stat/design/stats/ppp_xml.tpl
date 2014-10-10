<?xml version="1.0"?>
<statistics>
	<params>
		<logins>
			<login selected="{if (!$login)}1{else}0{/if}">
				<id></id>
				<name></name>
			</login>
{foreach from=$logins_all item=item}
			<login selected="{if $login==$item.id}1{else}0{/if}">
				<id>{$item.id}</id>
				<name>{$item.login}</name>
			</login>
{/foreach}
		</logins>
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
{foreach from=$stats item=item key=key name=outer}{if $item.login!="&nbsp;"}
		<line>
			<timestamp>{$item.ts1}</timestamp>
			<duration>{$item.ts2}</duration>
			<bytes_in>{$item.out_bytes}</bytes_in>
			<bytes_out>{$item.in_bytes}</bytes_out>
{if $detality=="sessions" || $detality=="login"}
			<login>{$item.login}</login>
{/if}
		</line>
{/if}{/foreach}
	</lines>
	<total>
		<duration>{$item.ts2}</duration>
		<bytes_in>{$item.out_bytes}</bytes_in>
		<bytes_out>{$item.in_bytes}</bytes_out>
{if $detality=="sessions" || $detality=="login"}
		<login>{$item.login}</login>
{/if}
	</total>
</statistics>
