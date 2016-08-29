<?php

/** @var [] $invoice */
/** @var string $invoiceContent */
?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style type="text/css">
            <?php readfile(Yii::$app->basePath . '/web/invoice.css'); ?>
        </style>
        <title>СЧЕТ-ФАКТУРА N <?= $invoice['bill']['id'] ?> от <?= $invoice['bill']['date'] ?> г.</title>
        <style>
            @page {
                size: landscape;
            }
            @page rotated {
                size: landscape;
            }
            .ht {
                font-size: 9pt;
            }
            .ht strong{
                font-size: 9pt;
            }
        </style>
    </head>

    <body bgcolor="#FFFFFF" text="#000000">

        <?= $invoiceContent ?>

    </body>
</html>
