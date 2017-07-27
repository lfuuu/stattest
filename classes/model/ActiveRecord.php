<?php

namespace app\classes\model;

use yii\behaviors\AttributeTypecastBehavior;

class ActiveRecord extends \yii\db\ActiveRecord
{
    protected $isAttributeTypecastBehavior = false;

    /**
     * @return array
     */
    public function behaviors()
    {
        return
            $this->isAttributeTypecastBehavior ?
                [
                    'typecast' => [
                        'class' => AttributeTypecastBehavior::className(),
                        'typecastAfterValidate' => false,
                        'typecastAfterFind' => true,
                    ],
                ] :
                [];
    }
}