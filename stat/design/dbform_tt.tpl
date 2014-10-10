<H3>Незакрытые заявки</H3>
{include file='tt/trouble_list.tpl'}
{if $tt_service}
	<a href='index.php?module=tt&clients_client={$tt_client}&service={$tt_service}&service_id={$tt_service_id}'>Создать заявку</a><br>
{/if}