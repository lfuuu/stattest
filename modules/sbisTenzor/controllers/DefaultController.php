<?php

namespace app\modules\sbisTenzor\controllers;

use app\classes\BaseController;

/**
 * Default controller for the `sbisTenzor` module
 */
class DefaultController extends BaseController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->redirect('/sbisTenzor/document/');
    }
}
