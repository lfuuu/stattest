<html>
  <head>
   
    <title>Акт N {$invoice.invoice_no} от {$invoice.invoice_date}</title>
  </head>
  <body bgcolor="#FFFFFF" text="#000000">
    <strong> {if $client.firma eq "mcn"}OOO "Эм Си Эн" {else}ООО "МАРКОМНЕТ" {/if}</strong>
    <br>
     Адрес:<strong>{if $client.firma eq "mcn"}113452 г. Москва, Балаклавский пр-т., д. 20, кор. 4 кв. 130{else}123458, г. Москва, Таллинская ул., д.2/282{/if}</strong>
    <br>
     Телефон:<strong>950-56-78</strong>
    <br>
    <br>
  
    <div align="center">
      <center>
        <h2>
           АКТ ПРИЕМА-ПЕРЕДАЧИ
        </h2>
        <br>
         по Договору <b>{$contract.contract_no}</b> от <b>{$contract.contract_date}</b>
        <br>
        <table border="0" cellpadding="0" cellspacing="15">
          <tr>
            <div align="center">
              <center>
                <table border="0" cellpadding="3" cellspacing="0" width="100%">
                  <tr>
                    <td align="left">
                       Мы, нижеподписавшиеся, {if $client.firma == 'mcn'}генеральный{/if} директор Мельников А.К. {if $client.firma != 'mcn'} ООО "Маркомнет"
			{else}OOO "Эм Си Эн"{/if} и представитель <strong>{$client.company_full},</strong> 
			{if $client.singer_name == ""}___________________________________________{else}{$client.singer_name}{/if}, произвели акт приема - 
			передачи во временное пользование следующего оборудования:<b> 
			{foreach from=$modem item=m key=key}
				{if $m == 'модем'} ADSL модем{else}{$m}{/if} кол-во {$k[$key]}шт.
			{/foreach}</b>
			<br> Получен залог в сумме <b>{$invoice.sum_plus_tax}</b> рублей.
               	    </td>
               	   </tr>
               	   </table>
               </center>
             </div>
                      <br>
                      <br>
                      <br>
                      <br>
                      <div style="position:relative; left:-80px; top:80px; z-index:1">
                         {if $client.stamp == 0 or $client.firma == 'mcn'}
                        <br>
                        <br>
                        <br>
                         {else}<img src="https://stat.mcn.ru/img/stamp1.gif" width="150" height="150" border="0" alt=""> {/if}
                      </div>
                       {if $client.stamp == 0 or $client.firma == 'mcn'} {assign var="pos" value="0"} {else} {assign var="pos" value="-150"} {/if}
                      <div style="position:relative; top:{$pos}px; z-index:10">
                        <table border="0" cellpadding="0" cellspacing="5">
                          <tr>
                            <td>
                              <p align="right">
                                 Сдал
                            
                            </td>
                            <td>
                               {if $client.stamp == 0 or $client.firma == 'mcn'}
                              <br>
                              <br>
                               ______________________________
                              <br>
                              <br>
                               {else}<img src="https://stat.mcn.ru/img/sign1.gif" width="155" height="80" border="0" alt="" align="top"> {/if}
                            </td>
                            <td>
                               &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            </td>
                            <td>
                              <p align="right">
                                 Принял
                            
                            </td>
                            <td>
                               ______________________________
                            </td>
                          </tr>
                          <tr>
                            <td>
                            </td>
                            <td align="center">
                              <small>(подпись)</small>
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td align="center">
                              <small>(подпись)</small>
                            </td>
                          </tr>
                          <tr>
                            <td>
                            </td>
                            <td align="center">
                              <br>
                              <br>
                               М.П.
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td align="center">
                              <br>
                              <br>
                               М.П.
                            </td>
                          </tr>
                        </table>
                      </div>
                    </td>
                  </tr>
                </table>
              </center>
            </div>
            </body>
            </html>


