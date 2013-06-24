<h1>Выгрузки из банк-клиента</h1>

<h2>Загрузить</h2>

<form enctype="multipart/form-data" action="?module=accounts&action=accounts_import_payments" method="post">
 <input type="hidden" name="MAX_FILE_SIZE" value="3000000" />
 <input type="hidden" name="todo" value="import" />
 Отправить этот файл: <input name="payments" type="file" />
 <input type="submit" value="Загрузить" />
</form>
<br>
<hr>
<br>
<h2>Смотреть выгрузки</h2>
Имя файла начинается на:<br>
<li><b>mar</b>  - выгрузка по маркомнет</li>
<li><b>mcn</b>  - выгрузка по mcn</li>


<form  action='?module=accounts&action=accounts_import_payments&todo=search' method="POST">
  День:<INPUT maxlength="2" size="2" value="{$day}" name="day" type="text">
  Месяц:<INPUT maxlength="2" size="2" value="{$month}" name="month" type="text">
  Год:<INPUT maxlength="4" size="4" value="{$year}" name="year" type="text">
  <INPUT value="Найти" name="submit" type="submit">
</form>

<table border="0"  cellpadding="5" >
<TR> 
{foreach from=$files item=file key=key}
{if $searches.$file == 1} 
	{assign var="color" value="#BDBBFF"} 
{else} 
	{assign var="color" value="#FFFFFF"} 
{/if}
<TD bgcolor="{$color}"><a href="modules/accounts/view_bank.php?file={$file}" target="_blank">{$file}</a></TD>
{if (($key+1) mod 4 ) == 0}
	</TR><tr>
{/if}
{/foreach}
</tr>
</table>


