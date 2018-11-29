<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use yii\base\InvalidParamException;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Class TroubleRoistatStore
 *
 * @property int $id
 * @property int $account_id
 * @property string $roistat_visit
 * @property string $created_at
 */
class TroubleRoistatStore extends ActiveRecord
{
    const STORE_TIME = 120; // 2минуты

    public function rules()
    {
        return [
            ['account_id', 'integer'],
            ['roistat_visit', 'string'],
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'tt_troubles_roistat_store';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => null,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @param $accountId
     * @return null|string
     */
    public static function getRoistatIdByAccountid($accountId)
    {
        self::clean();

        /** @var self $store */
        $store = self::find()
            ->where(['account_id' => $accountId])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();

        if (!$store) {
            return false;
        }

        $roistatId = $store->roistat_visit;

        $store->delete();

        return $roistatId;
    }

    /**
     * Очистка данных
     */
    private static function clean()
    {
        self::deleteAll('created_at < (NOW() - INTERVAL ' . self::STORE_TIME . ' second)');
    }
}
