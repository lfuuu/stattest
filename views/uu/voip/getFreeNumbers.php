<?php
/**
 * Вернуть массив свободных номеров
 *
 * @var \yii\web\View $this
 * @var \yii\db\ActiveQuery $numberActiveQuery
 * @var int $rowClass
 */

use app\classes\Html;

?>

<div class="row">
    <?php
    $isAnyShowed = false;
    /** @var \app\models\Number $number */
    foreach ($numbers as $number) :
        $isAnyShowed = true;
        ?>
        <div class="col-sm-<?= $rowClass ?>">
            <?= Html::checkbox('AccountTariffVoip[voip_numbers][]', false, [
                'value' => $number->number,
                'label' => $number->number,
            ]) ?>
            <?php
            $price = $number->price;
            $currency = $number->city->country->currency_id;
            ?>
            <?= is_null($price) ?
                'Договорная' :
                ($price ? sprintf('%d %s', $price, $currency) : 'Бесплатно')
            ?>
        </div>
        <?php
    endforeach;
    ?>
</div>

<?= $isAnyShowed ? '' : 'По запросу не найдено ни одного телефонного номера' ?>
