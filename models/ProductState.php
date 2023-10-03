<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Class ProductState
 * @package app\models
 *
 * @property string $product
 * @property int $client_id
 */
class ProductState extends ActiveRecord
{
    CONST FEEDBACK = 'feedback';
    CONST PHONE = 'phone';
    CONST VIRTPBX = 'virtpbx';

    public static function tableName()
    {
        return 'product_state';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::class,
        ];
    }


}
