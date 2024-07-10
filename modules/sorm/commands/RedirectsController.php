<?php

namespace app\modules\sorm\commands;

use app\modules\sorm\classes\redirects\RedirectCollectorDao;
use app\modules\sorm\classes\redirects\RedirectListenerDidFwd;
use app\modules\sorm\classes\redirects\RedirectListenerVpbxEvents;
use yii\console\Controller;

class RedirectsController extends Controller
{

    public function actionListenVpbxEvents($isReset = 0)
    {
        RedirectListenerVpbxEvents::me()->listen();
    }

    public function actionListenDidFwd($isReset = 0)
    {
        RedirectListenerDidFwd::me()->listen();
    }


    public function actionTest()
    {
        RedirectListenerVpbxEvents::me()->test();
    }

    public function actionTestDb()
    {
        RedirectListenerDidFwd::me()->test();
    }


    public function actionExport($did = null)
    {
        RedirectCollectorDao::me()->export($did);
    }

    public function actionGroup($did = null)
    {
        RedirectCollectorDao::me()->group($did);
    }
}
