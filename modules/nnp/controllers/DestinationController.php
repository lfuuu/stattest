<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\modules\nnp\filter\DestinationFilter;
use app\modules\nnp\forms\destination\FormEdit;
use app\modules\nnp\forms\destination\FormNew;
use app\modules\nnp\models\Destination;
use app\modules\nnp\Module;
use kartik\base\Config;
use Yii;
use yii\base\InvalidParamException;

/**
 * Направления
 */
class DestinationController extends BaseController
{
    /**
     * Список
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $filterModel = new DestinationFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Создать
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionNew()
    {
        /** @var FormNew $formModel */
        $formModel = new FormNew();

        // сообщение об ошибке
        if ($formModel->validateErrors) {
            Yii::$app->session->setFlash('error', $formModel->validateErrors);
        }

        if ($formModel->isSaved) {
            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'formModel' => $formModel,
        ]);
    }

    /**
     * Редактировать
     *
     * @param int $id
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionEdit($id)
    {
        /** @var FormEdit $formModel */
        $formModel = new FormEdit([
            'id' => $id
        ]);

        // сообщение об ошибке
        if ($formModel->validateErrors) {
            Yii::$app->session->setFlash('error', $formModel->validateErrors);
        }

        if ($formModel->isSaved) {
            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'formModel' => $formModel,
        ]);
    }

    /**
     * Развернуть в префиксы и скачать
     *
     * @param int $id
     * @return string
     * @throws \yii\web\RangeNotSatisfiableHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \BadMethodCallException
     * @throws \yii\base\InvalidParamException
     */
    public function actionDownload($id)
    {
        $id = (int)$id;
        if (!$id) {
            throw new InvalidParamException('Не указан id');
        }

        $destination = Destination::findOne(['id' => $id]);
        if (!$destination) {
            throw new InvalidParamException('Неправильный id');
        }

        /** @var Module $module */
        $module = Config::getModule('nnp');
        $prefixList = $module->getPrefixListByDestinationID($id);

        Yii::$app->response->sendContentAsFile(implode(PHP_EOL, $prefixList), $destination->name . '.csv');
        Yii::$app->end();
    }
}
