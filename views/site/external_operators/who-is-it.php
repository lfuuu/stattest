<?php
use yii\helpers\Html;
?>

<div class="well" style="padding-top: 60px;">
    <legend>Выбор оператора</legend>

    <div style="text-align: center;">
        <?php
        echo Html::beginForm('/site/who-is-it/?mode=do');

        foreach ($operators as $key => $value) {
            echo Html::submitButton($value, [
                'name' => 'operator',
                'value' => $key,
                'class' => 'btn btn-default',
                'style' => 'margin-right: 5px;',
            ]);
        }

        echo Html::endForm();
        ?>
    </div>

</div>