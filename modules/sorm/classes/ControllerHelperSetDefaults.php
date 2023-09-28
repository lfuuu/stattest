<?php

namespace app\modules\sorm\classes;

use app\classes\Singleton;

class ControllerHelperSetDefaults extends Singleton
{
    public function setDefaults(&$params, $formName = 'ClientsFilter', $regionFilterFields = 'filter_region_id')
    {
//        \Yii::$app->session->addFlash('success', var_export($params, true));
        $sessionStoreRegionKey = 'ClientsFilter[filter_region_id]';

        if (isset($params[$formName][$regionFilterFields])) {
            $_SESSION[$sessionStoreRegionKey] = $params[$formName][$regionFilterFields];
        } elseif (isset($_SESSION[$sessionStoreRegionKey])) {
            $params[$formName][$regionFilterFields] = $_SESSION[$sessionStoreRegionKey];
        }


        $amField = 'account_manager';
        $sessionStoreAmKey = 'ClientsFilter[' . $amField . ']';

        if (isset($params[$formName][$amField])) {
            $_SESSION[$sessionStoreAmKey] = $params[$formName][$amField];
        } elseif (isset($_SESSION[$sessionStoreAmKey])) {
            $params[$formName][$amField] = $_SESSION[$sessionStoreAmKey];
        }
    }

}