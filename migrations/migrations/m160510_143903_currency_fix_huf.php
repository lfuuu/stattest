<?php

class m160510_143903_currency_fix_huf extends \app\classes\Migration
{
    public function up()
    {
        $this->update(\app\models\CurrencyRate::tableName(), [
            'rate' => new \yii\db\Expression('rate / 100'),
        ], ['currency' => \app\models\Currency::HUF]);
    }

    public function down()
    {
        $this->update(\app\models\CurrencyRate::tableName(), [
            'rate' => new \yii\db\Expression('rate * 100'),
        ], ['currency' => \app\models\Currency::HUF]);
    }
}