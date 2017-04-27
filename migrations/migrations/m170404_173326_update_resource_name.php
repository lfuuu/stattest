<?php
use app\modules\uu\models\Resource;

/**
 * Class m170404_173326_update_resource_name
 */
class m170404_173326_update_resource_name extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->update(Resource::tableName(), ['name' => 'Запись звонков'], ['id' => Resource::ID_VPBX_RECORD]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->update(Resource::tableName(), ['name' => 'Запись звонков с сайта'], ['id' => Resource::ID_VPBX_RECORD]);
    }
}
