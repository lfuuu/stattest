<?php
namespace app\classes;

use Yii;
use yii\base\Exception;


class StatModule
{
    private static $map = [];

    public static function getHeadOrModule($moduleName)
    {
        if (isset(self::$map[$moduleName . '_head'])) {
            return self::$map[$moduleName . '_head'];
        }

        if (!class_exists('IModuleHead')) {
            require_once Yii::$app->basePath . '/stat/include/modules.php';
        }

        $path = Yii::$app->basePath . '/stat/modules/' . $moduleName . '/';

        $fileName = $path . 'header.php';
        if (file_exists($fileName)) {
            require_once $fileName;
            $className = 'm_' . $moduleName . '_head';
            $object = new $className();
            return self::$map[$moduleName . '_head'] = $object;
        }

        $fileName = $path . 'module.php';
        if (file_exists($fileName)) {
            require_once $fileName;
            $className = 'm_' . $moduleName;
            $object = new $className();
            return self::$map[$moduleName . '_head'] = self::$map[$moduleName]= $object;
        }

        throw new Exception("Невозможно подключить модуль " . $moduleName);
    }

    public static function getModule($moduleName)
    {
        if (isset(self::$map[$moduleName])) {
            return self::$map[$moduleName];
        }

        if (!class_exists('IModuleHead')) {
            require_once Yii::$app->basePath . '/stat/include/modules.php';
        }

        $path = Yii::$app->basePath . '/stat/modules/' . $moduleName . '/';

        $fileName = $path . 'module.php';
        if (file_exists($fileName)) {
            require_once $fileName;
            $className = 'm_' . $moduleName;
            $object = new $className();
            return self::$map[$moduleName] = $object;
        }

        throw new Exception("Невозможно подключить модуль " . $moduleName);
    }

    /**
     * @return \m_clients
     */
    public static function clients()
    {
        return self::getModule('clients');
    }

    /**
     * @return \m_users
     */
    public static function users()
    {
        return self::getModule('users');
    }

    /**
     * @return \m_tt
     */
    public static function tt()
    {
        return self::getModule('tt');
    }

    /**
     * @return \m_routers
     */
    public static function routers()
    {
        return self::getModule('routers');
    }

    /**
     * @return \m_newaccounts
     */
    public static function newaccounts()
    {
        return self::getModule('newaccounts');
    }

    /**
     * @return \m_services
     */
    public static function services()
    {
        return self::getModule('services');
    }

    /**
     * @return \m_monitoring
     */
    public static function monitoring()
    {
        return self::getModule('monitoring');
    }

}