<?php
/**
 * Вернуть массив свободных номеров
 *
 * @var \app\classes\BaseView $this
 * @var \yii\db\ActiveQuery $numberActiveQuery
 * @var int $rowClass
 * @var \app\models\Number[] $numbers
 */

use app\classes\Html;

?>

<div class="row">
    <?php
    $isAnyShowed = false;
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
            $currency = $number->city->country->currency;
            ?>
            <?= is_null($price) ?
                'Договорная' :
                ($price ? $currency->format($price) : 'Бесплатно')
            ?>
        </div>
        <?php
    endforeach;
    ?>
</div>

<?= $isAnyShowed ? '' : 'По запросу не найдено ни одного телефонного номера' ?>
