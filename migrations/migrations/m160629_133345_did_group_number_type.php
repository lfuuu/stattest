<?php

use app\models\DidGroup;
use app\models\NumberType;

class m160629_133345_did_group_number_type extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn(DidGroup::tableName(), 'number_type_id', $this->integer(11)->defaultValue(null));
        $this->addForeignKey(
            'fk-number_type_id',
            DidGroup::tableName(),
            'number_type_id',
            NumberType::tableName(),
            'id',
            $delete = 'SET NULL'
        );

        //Этот код понадобится в будущем :)
        /*
        foreach($this->db->createCommand("SELECT GROUP_CONCAT(DISTINCT did_group_id) as did_groups_id, number_type, count(*) FROM `voip_numbers` group by number_type")->queryAll() as $numberDidGroups) {
            $this->update(DidGroup::tableName(), ['number_type_id' => $numberDidGroups['number_type']],['id' => explode(',', $numberDidGroups['did_groups_id'])]);
        }
        */
        $this->update(DidGroup::tableName(), ['number_type_id' => NumberType::ID_GEO_DID]);
    }

    public function down()
    {
        $this->dropForeignKey('fk-number_type_id', DidGroup::tableName());
        $this->dropColumn(DidGroup::tableName(), 'number_type_id');
    }
}