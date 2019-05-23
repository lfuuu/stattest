<?php

use app\models\TroubleState;
use app\models\TroubleType;


/**
 * Class m190514_103805_add_new_stages
 */
class m190514_103805_add_new_stages extends \app\classes\Migration
{
    private function createStage($id, $order, $typeFolder, $typeStates, $stateName)
    {
        $pk = pow(2, $id - 4);
        $folder = $pk + 1;

        $typeFolder |= $folder;
        $typeStates |= $pk;

        $row = [
            'id' => $id,
            'pk' => $pk,
            'name' => $stateName,
            'order' => $order,
            'time_delta' => 1,
            'folder' => $folder,
            'deny' => 0,
            'state_1c' => null,
            'oso' => 0,
            'omo' => 0,
            'is_final' => 1,
            'is_in_popup' => 1,
        ];

        $this->insert(TroubleState::tableName(), $row);

        $this->update(TroubleType::tableName(), ['folders' => $typeFolder, 'states' => $typeStates], ['pk' => TroubleType::CONNECT]);
    }

    private function deleteStage($id, $typeFolder, $typeStates, $pk = TroubleType::CONNECT)
    {
        $this->delete(TroubleState::tableName(), ['id' => $id]);
        $this->update(TroubleType::tableName(), ['folders' => $typeFolder, 'states' => $typeStates], ['pk' => $pk]);
    }

    /**
     * Up
     */
    public function safeUp()
    {
        $this->createStage(65, 25, 2305772503030562817, 2305772503030562816, 'Биллинг');
        $this->createStage(66, 26, 4611615512244256769, 4611615512244256768, 'Внешнее ТТ');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->deleteStage(65, 2305772503030562817, 2305772503030562816);
        $this->deleteStage(66, 4611615512244256769, 4611615512244256768);
    }
}
