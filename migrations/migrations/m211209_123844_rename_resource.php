<?php

use app\classes\Migration;
use app\modules\uu\models\ResourceModel;

/**
 * Class m211209_123844_rename_resource
 */
class m211209_123844_rename_resource extends Migration
{
    public function safeUp()
    {
        $this->update(
            ResourceModel::tableName(),
            ['name' => 'Транк для внешней АТС'],
            ['id' => ResourceModel::ID_VPBX_TRUNK_EXT_VPBX]
        );
    }

    public function safeDown()
    {
        $this->update(
            ResourceModel::tableName(),
            ['name' => 'Гео-Автозамена'],
            ['id' => ResourceModel::ID_VPBX_TRUNK_EXT_VPBX]
        );
    }
}
