<?php

namespace app\modules\sorm\commands;

use app\modules\sorm\classes\sipDevice\Transfer;
use yii\console\Controller;
use yii\console\ExitCode;

class SipDeviceController extends Controller
{
    public function actionIndex()
    {
        Transfer::me()->go();

        return ExitCode::OK;
    }
}
