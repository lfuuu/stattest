<?php


namespace app\modules\sim\models;


use app\classes\model\ActiveRecord;
use Yii;
use yii\helpers\Url;

/**
 * @property int $id
 * @property int $partner_id
 * @property string $name
 * @property string $object_comment
 *
 */
class ImsiProfile extends ActiveRecord
{
    const ID_MSN_RUS = 6;

    const ID_MTT = 1;
    const ID_S1 = 2;
    const ID_S2 = 3;

    const ID_S6 = 8;
    const ID_S6_Global = 9;

    const IDS_OWN = [self::ID_MSN_RUS, self::ID_S1, self::ID_S2];


    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing_uu.sim_imsi_profile';
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
            [['id', 'partner_id',], 'required'],
            [['id', 'partner_id',], 'integer'],
            [['name', 'object_comment'], 'string'],
        ];
    }

    public function getImsies()
    {
        return $this->hasMany(Imsi::class, ['profile_id' => 'id'])
            ->indexBy('imsi');
    }

    public function getProfileNameList(){
        $names = ImsiProfile::find()->select('name')->asArray()->column();
        return $names;
    }

    /**
     * @param int $id
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public static function getUrlById($id)
    {
        return Url::to(['/sim/card-status/edit', 'id' => $id]);
    }
}