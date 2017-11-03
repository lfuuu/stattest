<?php
/**
 * SIM-карты. Список у клиента
 *
 * @var \app\classes\BaseView $this
 * @var int $client_account_id
 */

use app\modules\sim\models\Card;

$cards = Card::findAll(['client_account_id' => $client_account_id]);
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
            foreach ($cards as $card) {
                echo $card->getLink() . ' ';
            }
            ?>
        </div>
    </div>

</div>