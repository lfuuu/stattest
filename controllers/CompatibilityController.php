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
        if (!defined('PATH_TO_ROOT'))
        {
            define("PATH_TO_ROOT", Yii::$app->basePath . '/stat/');
        }

        require_once PATH_TO_ROOT . 'conf.php';

        global $user, $module, $modules, $design, $fixclient, $fixclient_data, $module_clients;

        ob_start();

        $design  = new \MySmarty();
        $user    = new \AuthUser();
        $modules = new \Modules();

        $user->AuthorizeByUserId(Yii::$app->user->id);

        $module = get_param_raw('module','clients');
        $action = get_param_raw('action','default');

        $design->assign('module', $module);

        if ($newClient = get_param_raw("clients_client"))
            $_SESSION["clients_client"] = $newClient;

        $fixclient = isset($_SESSION['clients_client']) ? $_SESSION['clients_client'] : '';
        $fixclient_data = array();

        if (isset($module_clients) && $module != 'clients' && $fixclient)
            $fixclient_data = $module_clients->get_client_info($fixclient);

        $design->assign('authuser', $user->_Data);
        $design->assign('user', $user);
        $design->assign('fixclient_data', $fixclient_data);
        $design->assign('fixclient', $fixclient);
        $design->assign('module', $module);


        $modules->GetMain($module, $action, $fixclient);

        if ($fixclient)
            $fixclient_data = $module_clients->get_client_info($fixclient);

        if (access('tt','view')) {
            if ((!$fixclient || $module != 'clients') && $module != 'tt') {
                $tt = new \m_tt();
                $tt->showTroubleList(2, 'top', $fixclient);
            }
        }

        $preOutput = ob_get_clean();

        ob_start();

        $renderLayout = $lite === false && !$design->ignore;

        if ($lite) {
            echo $this->view->render('/layouts/widgets/messages', [], $this);
        }

        if (!$design->ignore) {
            $design->ProcessEx('index_lite.tpl');
        }

        $output = ob_get_clean();

        if ($renderLayout && ($layoutFile = $this->findLayoutFile($this->getView())) !== false) {
            return $this->getView()->renderFile($layoutFile, ['content' => $preOutput . $output], $this);
        } else {
            return $preOutput . $output;
        }
    }
}
