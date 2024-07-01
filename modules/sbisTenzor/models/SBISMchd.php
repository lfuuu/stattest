<?php

namespace app\modules\sbisTenzor\models;

use app\classes\helpers\DependecyHelper;
use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use app\models\Organization;
use app\models\User;
use DateTime;
use DateTimeZone;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * МЧД (машиночитаемая доверенность)
 *
 * @property integer $id
 * @property string $mchd_number
 * @property string $mchd_xml
 * @property integer $sbis_organization_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property-read SBISOrganization $sbisOrganization
 */
class SBISMchd extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sbis_mchd';
    }

    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->db;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mchd_number' => 'МЧД номер доверенности',
            'mchd_xml' => 'МЧД',
            'sbis_organization_id' => 'Organization ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                // Установить "когда создал" и "когда обновил"
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression("UTC_TIMESTAMP()"), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSbisOrganization()
    {
        return $this->hasOne(SBISOrganization::class, ['id' => 'sbis_organization_id']);
    }


    public static function addMchd(SBISDocument $document, &$attachStruct)
    {
        if ($document->sbisOrganization->mchd) {
            $attachStruct['Сертификат'] = [
                'Доверенность' => [
                    'ИдентификаторМЧД' => $document->sbisOrganization->mchd->mchd_number,
                ]
            ];
        }
    }
}