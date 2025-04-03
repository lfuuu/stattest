<?php

namespace app\modules\sbisTenzor\models;

use app\classes\model\ActiveRecord;
use app\models\ClientAccount;
use app\modules\sbisTenzor\classes\SBISExchangeFile;
use app\modules\sbisTenzor\helpers\SBISInfo;
use Yii;

/**
 * Группа первичных документов для обмена в системе СБИС
 *
 * @property integer $id
 * @property string $name
 *
 * @property-read SBISExchangeGroupForm[] $groupForms
 */
class SBISExchangeGroup extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const ACT = 1;
    const ACT_AND_INVOICE_2016 = 2;
    const ACT_AND_INVOICE_2019 = 3;
    const ACT_AND_INVOICE_2025 = 4;

    /** @var SBISExchangeFile[] */
    protected $exchangeFiles;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sbis_exchange_group';
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
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Название',
        ];
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param ClientAccount $client
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getList(
        ClientAccount $client,
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {

        $groupIds = SBISInfo::getExchangeGroupsByClient($client);

        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['id' => SORT_ASC],
            ['id' => $groupIds]
        );
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroupForms()
    {
        return $this->hasMany(SBISExchangeGroupForm::class, ['group_id' => 'id'])
            ->inverseOf('group')
            ->indexBy('id');
    }

    /**
     * @return SBISExchangeFile[]
     */
    public function getExchangeFiles()
    {
        if (is_null($this->exchangeFiles)) {
            $this->exchangeFiles = [];

            foreach ($this->groupForms as $groupForm) {
                $this->exchangeFiles[] = new SBISExchangeFile($groupForm->form, SBISExchangeFile::EXTENSION_XML);
            }
//            foreach ($this->groupForms as $groupForm) {
//                $this->exchangeFiles[] = new SBISExchangeFile($groupForm->form, SBISExchangeFile::EXTENSION_PDF);
//            }
        }

        return $this->exchangeFiles;
    }
}