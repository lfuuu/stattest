<?php
namespace app\classes\traits;

use app\models\User;
use Yii;

/**
 * Методы для полей insert_time, insert_user_id, update_time, update_user_id
 *
 * model
 * // Методы для полей insert_time, insert_user_id, update_time, update_user_id
 * use \app\classes\traits\InsertUpdateUserTrait;
 */
trait InsertUpdateUserTrait
{
    use InsertUserTrait {
        InsertUserTrait::beforeSave as beforeSaveInsertUser;
    }

    use UpdateUserTrait {
        UpdateUserTrait::beforeSave as beforeSaveUpdateUser;
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->beforeSaveInsertUser($insert, false);
        $this->beforeSaveUpdateUser($insert, false);

        return parent::beforeSave($insert);
    }
}