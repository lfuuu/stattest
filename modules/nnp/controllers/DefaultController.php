<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;

/**
 * Default
 */
class DefaultController extends BaseController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->redirect('/nnp/number-range/');
    }

}
