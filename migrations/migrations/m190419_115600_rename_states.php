<?php

use app\models\TroubleFolder;
use \app\models\TroubleState;

/**
 * Class m190419_115600_rename_states
 */
class m190419_115600_rename_states extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $arr = [
            3 => 'Приостановлен',
            5 => 'Интернет',
            6 => 'Массовая проблема',
            8 => 'Welltime',
            13 => 'Межоператорское взаимодействие',
            14 => 'Разработка'
        ];

        foreach ($arr as $troubleStateId => $newName) {
            $folder = TroubleState::find()->select('(folder - 1) as folder')->where(['id' => $troubleStateId])->scalar();
            $isFolderExist = TroubleFolder::find()->where(['pk' => $folder])->exists();
            if (!$isFolderExist) {
                return false;
            }
            $this->update(TroubleState::tableName(), ['name' => $newName], ['id' => $troubleStateId]);
            $this->update(TroubleFolder::tableName(), ['name' => $newName], ['pk' => $folder]);
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $arr = [
            3 => ['Трабл УСПД', 'Тр УСПД'],
            5 => ['коллекТрабл', 'коллТр'],
            6 => ['массТрабл', 'массТр'],
            8 => ['Отработано', 'Отработано'],
            13 => ['Тех поддержка', 'Тех поддержка'],
            14 => ['Выдача', 'Выдача']
        ];

        foreach ($arr as $troubleStateId => $newName) {
            $folder = TroubleState::find()->select('(folder - 1) as folder')->where(['id' => $troubleStateId])->scalar();
            $isFolderExist = TroubleFolder::find()->where(['pk' => $folder])->exists();
            if (!$isFolderExist) {
                return false;
            }
            $this->update(TroubleState::tableName(), ['name' => $newName[0]], ['id' => $troubleStateId]);
            $this->update(TroubleFolder::tableName(), ['name' => $newName[1]], ['pk' => $folder]);
        }
    }
}
