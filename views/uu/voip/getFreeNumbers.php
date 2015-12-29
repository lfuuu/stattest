<?php
/**
 * Вернуть массив свободных номеров
 *
 * @var \yii\web\View $this
 * @var \yii\db\ActiveQuery $numberActiveQuery
 * @var int $rowClass
 */

use app\classes\Html;
use app\dao\NumberBeautyDao;

?>

<div class="row">
    <?php
    /** @var \app\models\Number $number */
    foreach ($numberActiveQuery->each() as $number) :
        ?>
        <div class="col-sm-<?= $rowClass ?>">
            <?= Html::checkbox('numberIds[]', false, [
                'value' => $number->number,
                'label' => $number->number,
            ]) ?>
            <?php
            $price = NumberBeautyDao::$beautyLvlPrices[$number->beauty_level];
            ?>
            <?= is_null($price) ?
                'По договоренности' :
                ($price ? sprintf('%d руб.', $price) : 'Бесплатно')
            ?>
        </div>
        <?php
    endforeach;
    ?>
</div>
