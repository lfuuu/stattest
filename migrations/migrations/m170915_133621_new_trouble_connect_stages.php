<?php
use app\models\TroubleState;
use app\models\TroubleType;

/**
 * Class m170915_133621_new_trouble_connect_stages
 */
class m170915_133621_new_trouble_connect_stages extends \app\classes\Migration
{
    public $newStages = [
        'Google',
        'Ручной ввод',
        'Звонок',
        'Чатофон',
        'Тестирование без менеджера',
        'Тестирование с менеджером',
        'Тестирование закончено',
        'Не дозвон'
    ];
    public $id = 51;

    public $typeFolder = 70231305224193;
    public $typeStates = 70231305224192;

    /**
     * Up
     */
    public function safeUp()
    {
        $this->update(TroubleState::tableName(), ['deny' => 0], ['id' => [41, 42, 44, 45, 46, 47, 48, 49]]);

        $order = 11;

        $id = $this->id;
        $folders = $this->typeFolder;
        $states = $this->typeStates;

        foreach ($this->newStages as $name) {
            $pk = pow(2, $id - 4);
            $folder = pow(2, $id - 4) + 1;

            $folders |= $folder;
            $states |= $pk;

            $row = [
                'id' => $id,
                'pk' => $pk,
                'name' => $name,
                'order' => $order++,
                'time_delta' => 1,
                'folder' => $folder,
                'deny' => 0,
                'state_1c' => null,
                'oso' => 0,
                'omo' => 0
            ];
            $id++;

            $this->insert(TroubleState::tableName(), $row);
        }

        $this->update(TroubleType::tableName(), ['folders' => $folders, 'states' => $states], ['pk' => TroubleType::CONNECT]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        foreach ([
                     41 => 7696581394432,
                     42 => 29274497089536,
                     44 => 44942537785344,
                     45 => 67482526154752,
                     46 => 65558380806144,
                     47 => 61297773248512,
                     48 => 52639119179776,
                     49 => 29274497089536
                 ] as $id => $denyValue) {
            $this->update(TroubleState::tableName(), ['deny' => $denyValue], ['id' => $id]);
        }

        $this->delete(TroubleState::tableName(), ['id' => range($this->id, $this->id + count($this->newStages))]);
        $this->update(TroubleType::tableName(), ['folders' => $this->typeFolder, 'states' => $this->typeStates], ['pk' => TroubleType::CONNECT]);
    }
}
