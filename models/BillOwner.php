<?php
namespace app\models;

use app\dao\CourierDao;
use yii\db\ActiveRecord;

/**
 * @property string $bill_no
 * @property int $owner_id
 * @property
 */
class BillOwner extends ActiveRecord
{
    public static function tableName()
    {
        return 'newbill_owner';
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $user = User::findOne($this->owner_id);
        $userName = $user ? $user->name : $this->owner_id;

        LogBill::dao()->log(
            $this->bill_no,
            $insert
                ? 'Установлен менеджер ' . $userName
                : 'Изменен менеджер на ' . $userName
        );
    }

    public function afterDelete()
    {
        parent::afterDelete();

        LogBill::dao()->log($this->bill_no, 'Удален менеджер');
    }


}