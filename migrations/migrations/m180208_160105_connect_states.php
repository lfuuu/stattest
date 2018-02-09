<?php

use app\models\TroubleState;
use app\models\TroubleType;

/**
 * Class m180208_160105_connect_states
 */
class m180208_160105_connect_states extends \app\classes\Migration
{
    private $_lastId = 58;
    private $_lastOrder = 18;

    private $_newStates = [
        'Выключен',
        'Выполнен',
        'Закрыт'
    ];

    private $_typeFolder = 35958290835832833;
    private $_typeStates = 35958290835832832;

        /**
     * Up
     */
    public function safeUp()
    {
        $id = $this->_lastId;
        $order = $this->_lastOrder;

        $typeFolder = $this->_typeFolder;
        $typeStates = $this->_typeStates;

        foreach ($this->_newStates as $stateName) {
            $id++;
            $order++;

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
                'omo' => 0
            ];

            $this->insert(TroubleState::tableName(), $row);
        }

        $this->update(TroubleType::tableName(), ['folders' => $typeFolder, 'states' => $typeStates], ['pk' => TroubleType::CONNECT]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(TroubleState::tableName(), ['>', 'id', $this->_lastId]);
        $this->update(TroubleType::tableName(), ['folders' => $this->_typeFolder, 'states' => $this->_typeStates], ['pk' => TroubleType::CONNECT]);
    }
}
