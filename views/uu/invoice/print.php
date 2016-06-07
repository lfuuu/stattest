<?php

use app\classes\DateFunction;
use app\classes\uu\model\AccountEntry;
use app\models\ClientAccount;

/** @var AccountEntry[] $accountEntries */
/** @var ClientAccount $clientAccount */
/** @var int $modePDF */

$firstEntry = $accountEntries[0];
?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style type="text/css">
            <?php readfile(Yii::$app->basePath . '/web/invoice.css'); ?>
        </style>
        <title>СЧЕТ-ФАКТУРА N <?= $firstEntry->bill_id ?> от <?= DateFunction::mdate($firstEntry->date, 'd.m.Y') ?> г.</title>
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

        <?= $this->render('invoice', [
            'accountEntries' => $accountEntries,
            'clientAccount' => $clientAccount,
        ]) ?>

    </body>
</html>
