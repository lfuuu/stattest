<?php
namespace app\classes;

use Yii;


class Navigation
{
    private $panelsData = [];

    /**
     * @return Navigation
     */
    public static function create()
    {
        if (!function_exists('access')) {
            include_once Yii::$app->basePath . '/classes/compatibility.php';
        }
        return new self();
    }

    public function getPanelsData()
    {
        return $this->panelsData;
    }

    private function __construct()
    {
        $this->addStatModule('clients');
        $this->addStatModule('services');
        $this->addStatModule('newaccounts');
        $this->addStatModule('newaccounts');
        $this->addStatModule('tt');
        $this->addStatModule('stats');
        $this->addStatModule('routers');
        $this->addStatModule('monitoring');
        $this->addStatModule('users');
        $this->addStatModule('usercontrol');
        $this->addStatModule('send');
        $this->addStatModule('employeers');
        $this->addStatModule('mail');
        $this->addStatModule('voipnew');
        $this->addStatModule('voipreports');
        $this->addStatModule('ats');
        $this->addStatModule('data');
        $this->addStatModule('incomegoods');
        $this->addStatModule('ats2');
        $this->addStatModule('logs');
    }

    private function addStatModule($moduleName)
    {
        $panelData = $this->getStatModulePanelData($moduleName);
        if ($panelData) {
            $this->panelsData[] = $panelData;
        }
    }


    private function getStatModulePanelData($moduleName)
    {
        $statModule = StatModule::getHeadOrModule($moduleName);

        $panelData = $statModule->GetPanel(null);
        if (!$panelData) {
            return null;
        }

        list($title, $items) = $panelData;

        return array(
            'module' => $moduleName,
            'title' => $title,
            'items' => $items,
        );
    }

}