<?php

namespace app\commands;

use app\classes\contragent\importer\lk\ContragentLkImporter;
use yii\console\Controller;

/**
 * Контрагенты. Конвертации и импорт.
 */

class ContragentController extends Controller
{

    public function actionImportFromLk($contragentId = null)
    {
        (new ContragentLkImporter())->run($contragentId);
    }
}
