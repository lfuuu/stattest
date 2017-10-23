<?php

namespace app\modules\uu;

use Yii;

/**
 * Универсальные услуги
 *
 * @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=4391334
 */
class Module extends \yii\base\Module
{
    const EVENT_VOIP_CALLS = 'uu_voip_calls';
    const EVENT_VOIP_INTERNET = 'uu_voip_internet';
    const EVENT_VPBX = 'uu_vpbx';
    const EVENT_CALL_CHAT = 'uu_chat';
    const EVENT_RESOURCE_VOIP = 'uu_resource_voip';
    const EVENT_RESOURCE_VPBX = 'uu_resource_vpbx';
    const EVENT_RECALC_ACCOUNT = 'uu_recalc_account';
    const EVENT_RECALC_BALANCE = 'uu_recalc_balance';
    const EVENT_VM_SYNC = 'uu_vm_sync';
    const EVENT_ADD_LIGHT = 'uu_add_light';
    const EVENT_DELETE_LIGHT = 'uu_delete_light';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\uu\controllers';

    /**
     * Для корректного запуска из консоли
     */
    public function init()
    {
        parent::init();
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\uu\commands';
        }
    }

}
