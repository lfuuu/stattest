<?php

namespace app\modules\sorm\commands;

use app\modules\sorm\classes\didDvo\DidDvoStarter;
use app\modules\sorm\classes\didDvo\DidDvoToCdr;
use app\modules\sorm\classes\didDvo\DidDvoToEvents;
use yii\console\Controller;
use yii\console\ExitCode;

class DidDvoController extends Controller
{
    public function actionIndex($isReset = 0)
    {
        (new DidDvoStarter())->go($isReset);

        return ExitCode::OK;
    }

    public function actionTest()
    {
        (new DidDvoStarter())->test();

        return ExitCode::OK;
    }

    public function actionToEvents($isReset = 0)
    {
        (new DidDvoToEvents())->go($isReset);

        return ExitCode::OK;
    }

    public function actionToEventsTest()
    {
        (new DidDvoToEvents())->test();

        return ExitCode::OK;
    }

    public function actionToCdr($isReset = 0)
    {
        (new DidDvoToCdr())->go($isReset);

        return ExitCode::OK;
    }

    public function actionToCdrTest()
    {
        (new DidDvoToCdr())->test();

        return ExitCode::OK;
    }
}
