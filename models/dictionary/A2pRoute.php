<?php

namespace app\models\dictionary;

use app\classes\model\ActiveRecord;
use app\classes\traits\GetListTrait;

/**
 * @property int $id
 * @property string $name
 */
class A2pRoute extends ActiveRecord
{
    use GetListTrait;

    public static function tableName()
    {
        return 'a2p_route';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
        ];
    }
}