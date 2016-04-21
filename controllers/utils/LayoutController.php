<?php
namespace app\controllers\utils;

use app\classes\BaseController;
use Yii;

class LayoutController extends BaseController
{
    /**
     * Запомнить, что надо показывать левый блок
     */
    public function actionShow()
    {
        $this->saveToSession(false);
    }

    /**
     * Запомнить, что надо скрывать левый блок
     */
    public function actionHide()
    {
        $this->saveToSession(true);
    }

    /**
     * Запомнить, надо ли скрывать левый блок
     * @param bool $isHideLeftLayout
     */
    private function saveToSession($isHideLeftLayout)
    {
        Yii::$app->session->set('isHideLeftLayout', $isHideLeftLayout);
    }
}
