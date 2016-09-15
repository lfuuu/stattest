<?php

namespace app\controllers\templates\uu;

use Yii;
use app\classes\BaseController;
use app\forms\templates\uu\InvoiceForm;

class InvoiceController extends BaseController
{

    /**
     * @return string
     */
    public function actionIndex()
    {
        $form = new InvoiceForm;

        if (Yii::$app->request->isPost && $form->save()) {
            Yii::$app->response->redirect('/templates/uu/invoice');
        }

        return $this->render('edit');
    }

    /**
     * @param string $langCode
     * @return string
     * @throws \yii\base\ExitException
     */
    public function actionGetContent($langCode)
    {
        $form = new InvoiceForm($langCode);

        echo $form->getFile();

        Yii::$app->end(200);
    }

    /**
     * @param string $langCode
     * @throws \yii\base\ExitException
     * @throws \yii\web\HttpException
     */
    public function actionDownloadContent($langCode)
    {
        $form = new InvoiceForm($langCode);

        if ($form->fileExists()) {
            Yii::$app->response->sendContentAsFile($form->getFile(), $langCode . '.' . $form::TEMPLATE_EXTENSION);
        }

        Yii::$app->end(200);
    }

}