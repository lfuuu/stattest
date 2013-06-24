<h2>Сеть с непрописанным тарифом </h2>

<table cellpadding="5" cellspacing="0" border="1">
	
	{foreach from=$nets item=net}
	<tr>
		<TD>{$net.client}</TD>
		<TD>{$net.net}</TD>
		<TD>{$net.port_id}</TD>
		<TD>{$net.tarif}</TD>
	</tr>
	{/foreach}
	
 </table>
