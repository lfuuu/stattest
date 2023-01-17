<?php


namespace app\modules\sim\models;


use app\classes\model\ActiveRecord;
use app\modules\sim\classes\TokenQrCodeMedia;
use Yii;

/**
 * @property int $imsi
 * @property string $token
 */
class ImsiToken extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing_uu.sim_imsi_token';
    }

    /**
     * @return string[]
     */
    public static function primaryKey()
    {
        return ['imsi'];
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {

        return Yii::$app->dbPgNnp;
    }

    public function rules()
    {
        return [
            [['imsi', 'token'], 'required'],
            [['imsi'], 'integer'],
            [['token'], 'string'],
        ];
    }

    public function getTokenQrCode()
    {
        return (new TokenQrCodeMedia())->generateImage($this);
    }
}