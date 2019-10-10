<?php

namespace app\controllers\dictionary;

use app\classes\BaseController;
use app\classes\DynamicModel;
use app\exceptions\ModelValidationException;
use app\models\dictionary\FormInfo;
use app\models\dictionary\FormInfoData;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\web\Response;

class DataController extends BaseController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'save'],
                        'roles' => ['dictionary.read'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     * @throws InvalidParamException
     */
    public function actionIndex()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        if (!\Yii::$app->request->isAjax) {
            return [];
        }

        $requestForm = DynamicModel::validateData(
            \Yii::$app->request->get(), [
            ['form_url', 'required'],
            ['form_url', 'string'],
        ]);

        $requestForm->validateWithException();

        $form = FormInfo::find()->where(['form_url' => $requestForm->form_url])->with('info')->one();

        if (!$form) {
            return [];
        }

        $data = [];
        foreach ($form->info as $info) {
            $data[$info->key] = [
                'url' => $info->url,
                'text' => nl2br($info->text),
            ];
        }

        return $data;
    }

    /**
     * @return array
     */
    public function actionSave()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        if (!\Yii::$app->request->isAjax) {
            return [];
        }

        $requestForm = DynamicModel::validateData(
            \Yii::$app->request->get(),
            [
            [['form_url', 'key'], 'required'],
            [['form_url', 'key', 'url', 'text'], 'string'],
            ['is_delete', 'integer'],
            ['is_delete', 'default', 'value' => 0],
        ]);

        $requestForm->validateWithException();

        $form = FormInfo::find()->where(['form_url' => $requestForm->form_url])->one();

        if (!$form) {
            $form = new FormInfo();
            $form->form_url = $requestForm->form_url;
            if (!$form->save()) {
                throw new ModelValidationException($form);
            }
        }

        $info = $form->getInfo()->where(['key' => $requestForm->key])->one();

        if ($info && $requestForm->is_delete) {
            $info->delete();
            return ['status' => 'ok'];
        }

        if (!$info) {
            $info = new FormInfoData();
            $info->form_id = $form->id;
            $info->key = $requestForm->key;
        }

        $info->url = $requestForm->url;
        $info->text = $requestForm->text;

        if (!$info->save()) {
            throw new ModelValidationException($info);
        }


        return ['status' => 'ok'];
    }
}