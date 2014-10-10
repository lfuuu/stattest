<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
	<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=utf-8">
	<TITLE>      </TITLE>

{literal}
	<STYLE>
	<!--
		@page { size: 27.94cm 12.07cm; margin-right: 1.27cm; margin-top: 0.64cm; margin-bottom: 1.27cm }
		P { margin-bottom: 0.21cm; direction: ltr; color: #000000; widows: 2; orphans: 2 }
		P.western { font-family: "Times New Roman", serif; font-size: 12pt }
		P.cjk { font-family: "Times New Roman", serif; font-size: 12pt }
		P.ctl { font-family: "Times New Roman", serif; font-size: 12pt; so-language: ar-SA }
		ADDRESS { margin-bottom: 0cm; direction: ltr; color: #000000; widows: 2; orphans: 2 }
		ADDRESS.western { font-family: "Arial", sans-serif; font-size: 10pt; font-style: normal }
		ADDRESS.cjk { font-family: "Times New Roman", serif; font-size: 10pt }
		ADDRESS.ctl { font-family: "Arial", sans-serif; font-size: 10pt; so-language: ar-SA }
		A:link { color: #0000ff }
        .envelope_to{margin-left: 12cm; font-size: 12pt}
        .envelope_from {margin-left: 6cm}
	-->
	</STYLE>
{/literal}
</HEAD>
<BODY LANG="ru-RU" TEXT="#000000" LINK="#0000ff" DIR="LTR">




<br><br><br>
<div class="envelope_from">
<IMG SRC="images/logo2.gif" NAME="Графический объект1" ALIGN=BOTTOM WIDTH=180 HEIGHT=79 BORDER=0><br>
{if $client.firma=="markomnet"}
О О О   &laquo;М а р к о м н е т&raquo;<BR>
{else}
О О О   &laquo;Эм Си Эн&raquo;<BR>
{/if}
117485, г.Москва, ул. Бутлерова 12 <br>
Телефон:   332-00-37, 950-5678  <br>
Факс: 332-00-27                      <br>
info@mcn.ru    http://www.mcn.ru          <br>
</div>
<br><br><br><br><br>
 <div class="envelope_to">
 <table border="0" style="font-size: 14pt" sellpadding="10">
 <tr>
 <td><b>Куда:</b></td><td style="font-size: 12pt">{$client.address_post_real}</td></tr>
 <tr><td><b> Кому:</b> </td><td style="font-size: 12pt">{$client.company_full}</td> </tr>
  <tr>
 <td><b></b></td><td style="font-size: 14pt">  </td>
 </tr>
 </table>

 </div>
</BODY>
</HTML>


