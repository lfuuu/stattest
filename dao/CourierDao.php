<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\Courier;
use yii\helpers\ArrayHelper;

/**
 * @method static CourierDao me($args = null)
 * @property
 */
class CourierDao extends Singleton
{

    public function getList($isWithEmpty = false, $depart = false)
    {
        $models = Courier::find()
            ->andWhere(['enabled' => 'yes'])
            ->orderBy('name');

        if ($depart) {
            $models = $models->andWhere(['depart' => 'Курьер']);
        }

        $list =
            ArrayHelper::map(
                $models->all(),
                'id',
                'name'
            );
        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }
        return $list;
    }

    public function getNameById($courierId)
    {
        $courier = Courier::findOne($courierId);
        return $courier ? str_replace("-", "", $courier->name) : '';
    }

}