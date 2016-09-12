<?php
namespace app\models;

use app\classes\api\ApiCore;
use yii\db\ActiveRecord;

/**
 * Class ClientSuper
 *
 * @property int $id
 * @property string $name
 * @property int $financial_manager_id
 * @property bool $is_lk_exists
 * @property ClientContragent[] $contragents
 */
class ClientSuper extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_super';
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'financial_manager_id' => 'Финансовый менеджер'
        ];
    }

    public function getContragents()
    {
        return $this->hasMany(ClientContragent::className(), ['super_id' => 'id']);
    }

    public function getContracts()
    {
        return $this->hasMany(ClientContract::className(), ['super_id' => 'id']);
    }

    public function getAccounts()
    {
        return $this->hasMany(ClientAccount::className(), ['super_id' => 'id']);
    }

    /**
     * Показывать ли ссылку перехода в ЛК
     *
     * @return bool|null
     */
    public function isShowLkLink()
    {
        if ($this->is_lk_exists) {
            return true;
        }

        try {
            $isLkExists = ApiCore::isLkExists($this->id);

            if ($isLkExists) {
                $this->is_lk_exists = 1;
                $this->save();
            }

            return $isLkExists;

        } catch (\Exception $e) {
            // возможно, не настроено API
            return null;
        }
    }

}
