<?php

use app\models\UserGrantGroups;

/**
 * Class m180304_154051_add_uu_user_right
 */
class m180304_154051_add_uu_user_right extends \app\classes\Migration
{
    private $_access = 'r,edit,addnew,activate,close,send_settings,e164,del4000';

    /**
     * Up
     */
    public function safeUp()
    {
        $this->update(
            UserGrantGroups::tableName(),
            [
                'access' => $this->_access . ',package',
            ],
            [
                'name' => UserGrantGroups::GROUP_ACCOUNT_MANAGER,
                'resource' => 'services_voip',
            ]
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->update(
            UserGrantGroups::tableName(),
            [
                'access' => $this->_access,
            ],
            [
                'name' => UserGrantGroups::GROUP_ACCOUNT_MANAGER,
                'resource' => 'services_voip',
            ]
        );
    }
}
