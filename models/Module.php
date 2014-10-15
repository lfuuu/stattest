<?php
namespace app\models;

use Yii;
use app\queries\ModuleQuery;
use yii\db\ActiveRecord;

/**
 * @property string $module
 * @property int $is_installed
 * @property int $load_order
 * @property
 */
class Module extends ActiveRecord
{

    public static function tableName()
    {
        return 'modules';
    }

    public static function find()
    {
        return new ModuleQuery(get_called_class());
    }

    public function getPanelData()
    {
        $statModule = $this->spawnStatModule();
        if (!$statModule) {
            return null;
        }

        $panelData = $statModule->GetPanel(null);
        if (!$panelData) {
            return null;
        }

        list($title, $items) = $panelData;

        return array(
            'module' => $this->module,
            'title' => $title,
            'items' => $items,
        );
    }

    private function spawnStatModule()
    {
        if ($className = $this->includeHeader()) {
            return new $className();
        }

        Yii::error("Невозможно подключить модуль " . $this->module);
        return null;
    }

    private function includeHeader()
    {
        if (!class_exists('IModuleHead')) {
            require_once Yii::$app->basePath . '/stat/include/modules.php';
        }

        $path = Yii::$app->basePath . '/stat/modules/' . $this->module . '/';

        $fileName = $path . 'header.php';
        if (file_exists($fileName)) {
            require_once $fileName;
            return 'm_' . $this->module . '_head';
        }

        $fileName = $path . 'module.php';
        if (file_exists($fileName)) {
            require_once $fileName;
            return 'm_' . $this->module;
        }

        return null;
    }

}