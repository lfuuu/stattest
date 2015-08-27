<?php

namespace app\dao\user;

use app\classes\Singleton;
use app\models\UserRight;
use yii\helpers\ArrayHelper;

/**
 * @method static UserRightDao me($args = null)
 * @property
 */
class UserRightDao extends Singleton
{

    public function getList()
    {
        $query = UserRight::find();

        $list = $query->orderBy('resource')->all();
        $result = [];
        foreach ($list as $resource) {
            preg_match('#^([^_]+)_?(.+)?#', $resource->resource, $match);
            $baseGroup = $match[1];

            $row = ArrayHelper::toArray($resource);
            $row['values'] = explode(',', $row['values']);
            $row['values_desc'] = explode(',', $row['values_desc']);
            $result[$baseGroup][$resource->resource] = $row;
        }

        return $result;
    }

}