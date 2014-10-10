<frameset rows="{$rows}">
{foreach from=$bills item=bill}
{if $do_bill}
	<frame src="?module=newaccounts&action=bill_print&bill={$bill.0}&obj=bill&curr=USD&source=2" name="" marginwidth="10" marginheight="10">
{/if}
{if $bill.1}
	{if $do_inv}
		<frame src="?module=newaccounts&action=bill_print&bill={$bill.0}&obj=invoice&source=1" name="" marginwidth="10" marginheight="10">
	{/if}{if $do_akt}
		<frame src="?module=newaccounts&action=bill_print&bill={$bill.0}&obj=akt&source=1" name="" marginwidth="10" marginheight="10">
	{/if}
{/if}
{if $bill.2}	
	{if $do_inv}
		<frame src="?module=newaccounts&action=bill_print&bill={$bill.0}&obj=invoice&source=2" name="" marginwidth="10" marginheight="10">
	{/if}{if $do_akt}
		<frame src="?module=newaccounts&action=bill_print&bill={$bill.0}&obj=akt&source=2" name="" marginwidth="10" marginheight="10">
	{/if}
{/if}
{if $bill.3}
	{if $do_inv}
		<frame src="?module=newaccounts&action=bill_print&bill={$bill.0}&obj=invoice&source=3" name="" marginwidth="10" marginheight="10">
	{/if}{if $do_akt}
		<frame src="?module=newaccounts&action=bill_print&bill={$bill.0}&obj=akt&source=3" name="" marginwidth="10" marginheight="10">
	{/if}
{/if}
{/foreach}
</frameset>
