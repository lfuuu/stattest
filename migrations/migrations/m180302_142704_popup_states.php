<?php

use app\models\TroubleFolder;
use app\models\TroubleState;
use app\models\TroubleType;

/**
 * Class m180302_142704_popup_states
 */
class m180302_142704_popup_states extends \app\classes\Migration
{
    private $_lastId = 61;
    private $_lastOrder = 21;

    private $_newStates = [
        'Консультация тех. поддержки',
        'Консультация абон. отдела',
    ];

    private $_typeFolder = 288159869968580609;
    private $_typeStates = 288159869968580608;

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
                'omo' => 0,
                'is_final' => 1,
                'is_in_popup' => 1,
            ];

            $this->insert(TroubleState::tableName(), $row);

            $folder = [
                'pk' => $pk,
                'name' => $stateName,
                'order' => $order - 1,
            ];

            $this->insert(TroubleFolder::tableName(), $folder);
        }

        $this->update(TroubleType::tableName(), ['folders' => $typeFolder, 'states' => $typeStates], ['pk' => TroubleType::CONNECT]);

        $this->update(TroubleState::tableName(), ['is_in_popup' => 0], ['id' => TroubleState::CONNECT__TRASH]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $troublePks = TroubleState::find()->where(['>', 'id', $this->_lastId])->select('pk')->column();

        if ($troublePks) {
            $this->delete(TroubleFolder::tableName(), ['pk' => $troublePks]);
        }

        $this->delete(TroubleState::tableName(), ['>', 'id', $this->_lastId]);
        $this->update(TroubleType::tableName(), ['folders' => $this->_typeFolder, 'states' => $this->_typeStates], ['pk' => TroubleType::CONNECT]);

        $this->update(TroubleState::tableName(), ['is_in_popup' => 1], ['id' => TroubleState::CONNECT__TRASH]);
    }
}
