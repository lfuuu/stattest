<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
    <head>
        <meta http-equiv="CONTENT-TYPE" content="text/html; charset=UTF-8">
        <title>      </title>

        <style type="text/css">
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
        .envelope_to{margin-left: 10cm; font-size: 12pt; padding-top: 26.5cm;}
        .envelope_from {margin-left: 6cm}
        </style>
    </head>
    <body lang="ru-RU" text="#000000" link="#0000ff" dir="LTR">

        <div class="envelope_to">
            <table border="0" style="font-size: 12pt" cellpadding="3">
                <tr>
                    <td valign=top><b>Куда:</b></td>
                    <td style="font-size: 12pt"><?= $account->address_post_real ?></td>
                </tr>
                <tr>
                    <td valign=top><b> Кому:</b></td>
                    <td style="font-size: 12pt">
                        <?= ($account->mail_who) ? $account->mail_who : $account->contract->contragent->name_full;?>
                    </td>
                </tr>
            </table>
        </div>

    </body>
</html>


