<?php
namespace app\modules\glpi\commands;

use app\modules\glpi\classes\Glpi;
use yii\console\Controller;

class ApiController extends Controller
{
    /**
     * @return int
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidCallException
     */
    public function actionGetAllItems()
    {
        $glpi = Glpi::me();

        // Инициализировать сессию
        $glpi->initSession();

        // Получить все заявки (проблемы)
        $items = $glpi->getAllItems();
        foreach ($items as $item) {
            print_r(get_object_vars($item));
        }

        // Удалить сессию
        $glpi->killSession();

        echo PHP_EOL;
        return Controller::EXIT_CODE_NORMAL;
    }
}
