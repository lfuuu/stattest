<?php

namespace app\controllers;

use Yii;
use app\classes\BaseController;
use app\classes\Encoding;

class CompatibilityController extends BaseController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        return $this->runOldStat();
    }

    public function actionLite()
    {
        return $this->runOldStat(true);
    }

    private function runOldStat($lite = false)
    {
        define("PATH_TO_ROOT", Yii::$app->basePath . '/stat/');
        require_once PATH_TO_ROOT . 'conf.php';

        global $user, $module, $modules, $design, $fixclient, $fixclient_data, $module_clients;

        $user->AuthorizeByUserId(Yii::$app->user->id);

        $module = get_param_raw('module','clients');
        $action = get_param_raw('action','default');

        $design->assign('module', $module);
        $design->AddMain('errors.tpl');


        $fixclient = isset($_SESSION['clients_client']) ? $_SESSION['clients_client'] : '';
        $fixclient_data = array();

        if (isset($module_clients) && $module != 'clients' && $fixclient)
            $fixclient_data = $module_clients->get_client_info($fixclient);

        $modules->GetMain($module, $action, $fixclient);

        if ($fixclient)
            $fixclient_data = $module_clients->get_client_info($fixclient);

        if (access('tt','view')) {
            if (!($fixclient && $module == 'clients')) {
                $tt = new \m_tt();
                $tt->showTroubleList(2,'top',$fixclient);
            }
        }

        ob_start();
        $design->ProcessEx('index_lite.tpl');
        $output = ob_get_clean();


        $layoutFile = $this->findLayoutFile($this->getView());
        if ($lite === false && $layoutFile !== false) {
            return $this->getView()->renderFile($layoutFile, ['content' => $output], $this);
        } else {
            return $output;
        }
    }
}