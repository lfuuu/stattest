<?php

namespace app\classes\model;

use yii\behaviors\AttributeTypecastBehavior;

class ActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::className(),
                'typecastAfterValidate' => false,
                'typecastAfterFind' => true,
            ],
        ];
    }
}