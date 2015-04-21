<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $account_id
 * @property int $step
 * @property string $state
 */
class LkWizardState extends ActiveRecord
{
    public static function tableName()
    {
        return 'lk_wizard_state';
    }

    public function getStepName()
    {
        switch($this->step)
        {
            case 1: return "Заполнение реквизитов"; break;
            case 2: return "Скачивание договора"; break;
            case 3: return "Загрузка договора"; break;
            case 4: $s = "Ожидание проверки"; 
            switch ($this->state)
            {
                case 'approve': $s = "Документы проверенны"; break;
                case 'rejected': $s = "Проверка не пройдена"; break;
            }
            return $s;
        }
    }
}
