<?php
/**
 * SIM-карты. Список у клиента
 *
 * @var \app\classes\BaseView $this
 * @var int $client_account_id
 */

use app\classes\Html;
use app\modules\sim\models\Card;

$cards = Card::find()
    ->where(['client_account_id' => $client_account_id])
    ->with(['imsies', 'imsies.number'])
    ->all();

if (!$cards) {
    return;
}
?>

<div class="col-sm-4">

    <div class="panel panel-danger">
        <div class="panel-heading">
            <h2 class="panel-title">SIM-карты</h2>
        </div>

        <div class="panel-body">
            <?php
            /** @var Card $card */
            foreach ($cards as $card) {
                echo $card->getLink() ;

                $cnt = 0;

                if(!($imsies = $card->imsies)) {
                    echo ' ';
                    continue;
                }

                echo Html::beginTag('small');
                foreach ($imsies as $imsi) {
                    if ($imsi->msisdn) {
                        if (!$cnt) {
                            echo '&nbsp;(';
                        }

                        if ($cnt) {
                            echo ', ';
                        }
                        $cnt++;

                        echo $imsi->number->link;
                    }
                }

                if ($cnt) {
                    echo ')';
                }
                echo Html::endTag('small') . ' ';
            }
            ?>
        </div>
    </div>

</div>