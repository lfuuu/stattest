<?php

use app\models\TroubleState;
use app\models\TroubleType;

/**
 * Class m190304_121655_add_new_state
 */
class m190304_121655_add_new_state extends \app\classes\Migration
{
    private $_typeFolder = 1152850998423715841;
    private $_typeStates = 1152850998423715840;

    private $_id = 64;

    /**
     * Up
     */
    public function safeUp()
    {
        $order = 24;

        $typeFolder = $this->_typeFolder;
        $typeStates = $this->_typeStates;

        $stateName = 'Допродажа';

        $pk = pow(2, $this->_id - 4);
        $folder = $pk + 1;


        $typeFolder |= $folder;
        $typeStates |= $pk;

        $row = [
            'id' => $this->_id,
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

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(TroubleState::tableName(), ['id' => $this->_id]);
        $this->update(TroubleType::tableName(), ['folders' => $this->_typeFolder, 'states' => $this->_typeStates], ['pk' => TroubleType::CONNECT]);
    }
}
