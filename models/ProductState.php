<?php
namespace app\models;

use yii\db\ActiveRecord;
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

}
