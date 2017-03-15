<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\Courier;

/**
 * @method static CourierDao me($args = null)
 */
class CourierDao extends Singleton
{
    /**
     * @param int $courierId
     * @return string
     */
    public function getNameById($courierId)
    {
        $courier = Courier::findOne($courierId);
        return $courier ? str_replace("-", "", $courier->name) : '';
    }

}