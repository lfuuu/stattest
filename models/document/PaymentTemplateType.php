<?php

namespace app\models\document;

use app\classes\model\ActiveRecord;
use app\modules\uu\models\Tariff;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\Url;
use app\models\User;

/**
 * This is the model class for table "payment_template_type".
 *
 * @property integer $id
 * @property string $name
 * @property string $note
 * @property integer $is_enabled
 * @property string $created_at
 * @property string $updated_at
 * @property integer $updated_by
 * @property integer $is_portrait
 * @property string $data_source
 *
 * @property PaymentTemplate[] $paymentTemplates
 * @property User $updatedBy
 *
 * @property-readonly Tariff[] $tariffs
 */
class PaymentTemplateType extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const TYPE_INVOICE = 1;
    const TYPE_INVOICE_STORNO = 2;
    const TYPE_INVOICE_RF = 3;
    const TYPE_INVOICE_PROFORMA = 4;

    const TYPE_ID_UPD = 14;

    const TYPE_PORTRAIT = 1;
    const TYPE_LANDSCAPE = 0;
    const DATA_SOURCE_INVOICE = 'invoice';
    const DATA_SOURCE_BILL = 'bill';
    const DATA_SOURCE_TARIFF = 'tariff';
    const DATA_SOURCE_UPD = 'upd';

    public static $typeList = [
        self::TYPE_PORTRAIT => 'Портретная',
        self::TYPE_LANDSCAPE => 'Ландшафтная',
    ];

    public static $dataSourceList = [
        self::DATA_SOURCE_INVOICE => 'Счет-фактуры/Акты',
        self::DATA_SOURCE_BILL => 'Счета',
        self::DATA_SOURCE_TARIFF => 'Тарифы',
        self::DATA_SOURCE_UPD => 'УПД',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_template_type';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                [
                    // Установить "когда создал" и "когда обновил"
                    'class' => TimestampBehavior::class,
                    'value' => new Expression('UTC_TIMESTAMP()'), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
                ],
                [
                    // Установить "кто создал" и "кто обновил"
                    'class' => AttributeBehavior::class,
                    'attributes' => [
                        ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_by',
                    ],
                    'value' => Yii::$app->user->getId(),
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'is_enabled', 'created_at', 'is_portrait', 'data_source'], 'required'],
            [['created_at', 'updated_at', 'is_enabled'], 'safe'],
            [['updated_by'], 'integer'],
            [['name', 'note'], 'string', 'max' => 255],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
            [['is_portrait'], 'integer'],
            [['data_source'], 'string'],
            [['short_name'], 'string'],
            ['is_enabled', 'validateIsEnabled']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'note' => 'Заметки',
            'is_enabled' => 'Активен',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлён',
            'updated_by' => 'Изменён',
            'is_portrait' => 'Ориентация',
            'data_source' => 'Источник данных',
            'short_name' => 'Короткое название',
        ];
    }

    /**
     * Какие поля не показывать в исторических данных
     *
     * @param string $action
     * @return string[]
     */
    public static function getHistoryHiddenFields($action)
    {
        return [
            'id',
            'created_at',
            'updated_at',
            'updated_by',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentTemplates()
    {
        return $this->hasMany(PaymentTemplate::class, ['type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTariffs()
    {
        return $this->hasMany(Tariff::class, ['id' => 'payment_template_type_id']);
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $dataSource = null
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['id' => SORT_ASC],
            $where = ['is_enabled' => 1] + ($dataSource ? ['data_source' => $dataSource] : [])
        );
    }

    public function validateIsEnabled($attribute, $params)
    {
        $dirtyAttributes = $this->getDirtyAttributes();

        if (!isset($dirtyAttributes[$attribute])) {
            return true;
        }

        // включение не проверяем
        if ($this->$attribute) {
            return true;
        }

        // не используется
        $tariffs = Tariff::find()->where(['payment_template_type_id' => $this->id])->limit(10)->all();
        if(!$tariffs) {
            return true;
        }

        $this->addError($attribute, 'Шаблон используется в тарифах: ' . implode(', ', array_map(fn(Tariff $tariff) => $tariff->link, $tariffs)));

        return false;
    }

    public function getTemplateContent($countryId)
    {
        $query = $this->hasOne(PaymentTemplate::class, ['type_id' => 'id'])
            ->andWhere(['is_active' => 1, 'country_code' => $countryId])
            ->orderBy(['version' => SORT_DESC]);

        /** @var PaymentTemplate $templateModel */
        $templateModel = $query->one();
        if ($templateModel) {
            return $templateModel->content;
        }

        return '';
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['/dictionary/payment-template-type/edit', 'id' => $this->id]);
    }

    /**
     * @return string
     */
    public function getToggleEnableUrl()
    {
        return Url::to(['/dictionary/payment-template-type/toggle-enable', 'id' => $this->id]);
    }

    public function __toString()
    {
        return $this->name;
    }
}
