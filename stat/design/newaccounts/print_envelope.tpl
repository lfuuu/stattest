<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
	<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=UTF-8">
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
        .envelope_to{margin-left: 9cm; font-size: 12pt; padding-top: 26.5cm;}
        .envelope_from {margin-left: 6cm}
	-->
	</STYLE>
{/literal}
</HEAD>
<BODY LANG="ru-RU" TEXT="#000000" LINK="#0000ff" DIR="LTR">

 <div class="envelope_to">
 <table border="0" style="font-size: 12pt" cellpadding="3">
 <tr><td valign=top><b>Куда:</b></td><td style="font-size: 12pt">{$client.address_post_real}</td></tr>
 <tr><td valign=top><b> Кому:</b> </td><td style="font-size: 12pt">{if $client.mail_who}{$client.mail_who}{else}{$client.company_full}{/if}</td> </tr>
 </table>

 </div>
</BODY>
</HTML>


