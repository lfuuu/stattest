<?php

namespace app\controllers;

use app\classes\StatModule;
use app\models\User;
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

        global $user, $module, $design, $fixclient, $fixclient_data;

        ob_start();

        $design  = new \MySmarty();
        $user    = new \AuthUser();

        $user->AuthorizeByUserId(Yii::$app->user->id);

        $module = get_param_raw('module','clients');
        $action = get_param_raw('action','default');

        $design->assign('module', $module);

        if ($newClient = get_param_raw("clients_client"))
            $_SESSION["clients_client"] = $newClient;

        if (Yii::$app->user->identity->restriction_client_id) {
          $fixclient = Yii::$app->user->identity->restriction_client_id;
        } elseif($cc = Yii::$app->request->get('clients_client')) {
            $fixclient = $cc;
        }
        else {
          $fixclient = isset($_SESSION['clients_client']) ? $_SESSION['clients_client'] : 0;
        }
        $fixclient_data = array();

        if ($fixclient) {
            $this->applyFixClient($fixclient);
        }

        $design->assign('authuser', $user->_Data);
        $design->assign('user', $user);
        $design->assign('fixclient_data', $fixclient_data);
        $design->assign('fixclient', $fixclient);
        $design->assign('module', $module);

        StatModule::getHeadOrModule($module)->GetMain($action, $fixclient);

        $renderLayout = $lite === false && !$design->ignore;

        if ($fixclient) {
            $this->applyFixClient($fixclient);
        }

        if (
            $renderLayout && access('tt','view')
            && Yii::$app->user->getIdentity()->hasAttribute('show_troubles_on_every_page')
            && Yii::$app->user->getIdentity()->show_troubles_on_every_page > 0
        ) {
            if ((!$fixclient || $module != 'clients') && $module != 'tt') {
                $tt = new \m_tt();
                $tt->showTroubleList(2, 'top', $fixclient);
            }
        }

        $preOutput = ob_get_clean();

        ob_start();

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
