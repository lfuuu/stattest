<?php

namespace app\modules\nnp\classes;

use app\classes\Singleton;
use app\modules\nnp\models\City;
use app\modules\nnp\models\Country;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\Operator;
use app\modules\nnp\models\Region as nnpRegion;
use yii\db\ActiveQuery;

class NnpToRedis extends Singleton
{
    public function refillAll()
    {
        $this->refillOperator();

        $this->_redisSet(Country::find()->asArray(), 'country', 'code');
        $this->_redisSet(Country::find()->asArray(), 'countryEn', 'code', 'name_eng');

        $this->_redisSet(City::find()->asArray(), 'city');
        $this->_redisSet(City::find()->asArray(), 'cityEn', 'id', 'name_translit');

        $this->_redisSet(nnpRegion::find()->asArray(), 'region');
        $this->_redisSet(nnpRegion::find()->asArray(), 'regionEn', 'id', 'name_translit');

        $this->_redisSet(NdcType::find()->asArray(), 'ndcType');
    }

    public function refillOperator()
    {
        $this->_redisSet(Operator::find()->asArray(), 'operator');
        $this->_redisSet(Operator::find()->asArray(), 'operatorEn', 'id', 'name_translit');
    }

    private function _redisSet(ActiveQuery $query, $prefix, $id = 'id', $name = 'name')
    {
        echo PHP_EOL . $prefix;

        /** @var yii\redis\Cache $redis */
        $redis = \Yii::$app->redis2;

        foreach ($query->each() as $o) {
            $redis->set($prefix . ':' . $o[$id], $o[$name]);
        }
    }

}