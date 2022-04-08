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
 * Организация для работы в системе СБИС
 *
 * @property integer $id
 * @property integer $organization_id
 * @property integer $is_active
 * @property string $exchange_id
 * @property integer $is_sign_needed
 * @property string $thumbprint
 * @property string $algorithm
 * @property string $date_of_expire
 * @property string $last_event_id
 * @property string $previous_event_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $last_fetched_at
 * @property integer $updated_by
 *
 * @property-read User $updatedBy
 * @property-read Organization $organization
 */
class SBISOrganization extends ActiveRecord
{
    const ID_MCN_TELECOM = 1;
    const ID_MCN_TELECOM_SERVICE = 2; // МСН Телеком Сервис

    public $login;
    public $password;
    public $authUrl;
    public $serviceUrl;
    public $signCommand;
    public $hashCommand;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sbis_organization';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['organization_id', 'is_active', 'is_sign_needed', 'thumbprint', 'date_of_expire'], 'required'],
            [['organization_id', 'updated_by'], 'integer'],
            [['date_of_expire', 'created_at', 'updated_at', 'last_fetched_at', 'is_active', 'is_sign_needed'], 'safe'],
            [['exchange_id'], 'string', 'max' => 46],
            [['thumbprint'], 'string', 'max' => 2048],
            [['algorithm'], 'string', 'max' => 32],
            [['last_event_id', 'previous_event_id'], 'string', 'max' => 36],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
            [
                ['organization_id', 'is_active'], 'unique', 'targetAttribute' => ['organization_id', 'is_active'],
                'message' => 'Данная организация {value} для работы со СБИС уже есть и активна.'
            ],
        ];
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
            'organization_id' => 'Organization ID',
            'is_active' => 'Is Active',
            'is_sign_needed' => 'Is Sign Needed',
            'thumbprint' => 'Thumbprint',
            'algorithm' => 'Algorithm',
            'date_of_expire' => 'Date Of Expire',
            'last_event_id' => 'Last Event ID',
            'previous_event_id' => 'Previous Event ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'last_fetched_at' => 'Last Fetched At',
            'updated_by' => 'Updated By',
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
            [
                // Установить "кто создал" и "кто обновил"
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['updated_by'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_by',
                ],
                'value' => Yii::$app->user->getId(),
            ],
        ];
    }

    /**
     * @return Organization|\yii\db\ActiveRecord|null
     */
    public function getOrganization()
    {
        return Organization::find()->byId($this->organization_id)->actual()->one();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * Проверка валидности подписи
     *
     * @throws \Exception
     */
    public function checkExpirationDate()
    {
        $dateExpiration = new DateTime(
            $this->date_of_expire,
            new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)
        );
        $dateNextMonth = new DateTime('+1 month 1 week');
        if ($dateExpiration < $dateNextMonth) {
            $cacheKey = $this->getCacheCheckKey();
            if (!Yii::$app->cache->exists($cacheKey)) {
                // отправляем 1 раз в день
                Yii::$app->cache->set($cacheKey, 1, DependecyHelper::TIMELIFE_DAY);

                Yii::$app->mailer
                    ->compose()
                    ->setTextBody(sprintf(
                        'Warning!' . PHP_EOL . PHP_EOL .
                        'SBIS signature (thumbprint: %s) for SBISOrganization #%s (%s) is gonna be expired on: %s' . PHP_EOL . PHP_EOL .
                        'Please get the new one, install and update the key info!',
                        $this->thumbprint,
                        $this->id,
                        strval($this->organization->name),
                        $dateExpiration->format(DateTimeZoneHelper::DATE_FORMAT)
                    ))
                    ->setFrom(Yii::$app->params['adminEmail'])
                    ->setTo([
//                        'shvedov@mcn.ru' => 'Ilya',
                        'adima@mcn.ru' => 'Dima',
                        'mak@mcn.ru' => 'Alexander',
                        'vma@mcn.ru' => 'Mikhail',
                    ])
                    ->setSubject('SBIS signature is gonna be expired')
                    ->send();
            }
        }
    }

    /**
     * @return string
     */
    protected function getCacheCheckKey()
    {
        return self::class . '_check_' . $this->id;
    }
}