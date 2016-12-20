<?php

namespace app\controllers;

use Yii;
use app\exceptions\FormValidationException;
use app\classes\DynamicModel;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class NotificationController extends Controller
{

    public $enableCsrfValidation = false;

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    /**
     * @inheritdoc
     * @throws \yii\base\ExitException
     */
    public function actionIndex()
    {
        $token = Yii::$app->params['NOTIFICATION_TOKEN'];

        try {
            if (!isset($token)) {
                throw new InvalidConfigException('Token not configured');
            }

            $input = DynamicModel::validateData(
                Yii::$app->request->bodyParams,
                [
                    [['token', 'event_type'], 'required'],
                    [['token', 'event_type'], 'string'],
                    [['abon', 'did'], 'string'],
                ]
            );

            if ($input->hasErrors()) {
                throw new FormValidationException($input);
            }

            if ($input->token !== $token) {
                throw new ForbiddenHttpException('Token is invalid');
            }

            if (!method_exists($this, $input->event_type)) {
                throw new InvalidCallException('Event not found');
            }
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }

        return ['result' => $this->{$input->event_type}($input)];
    }

    /**
     * @param DynamicModel $input
     */
    private function onInCallingStart(DynamicModel $input)
    {
        Yii::info('Notification::InCallingStart ' . var_export($input->getAttributes(), true));
        return true;
    }

    /**
     * @param DynamicModel $input
     */
    private function onInCallingEnd(DynamicModel $input)
    {
        Yii::info('Notification::InCallingEnd ' . var_export($input->getAttributes(), true));
        return true;
    }

    /**
     * @param DynamicModel $input
     */
    private function onOutCallingStart(DynamicModel $input)
    {
        Yii::info('Notification::OutCallingStart ' . var_export($input->getAttributes(), true));
        return true;
    }

    /**
     * @param DynamicModel $input
     */
    private function onOutCallingEnd(DynamicModel $input)
    {
        Yii::info('Notification::OutCallingEnd ' . var_export($input->getAttributes(), true));
        return true;
    }

    /**
     * @param DynamicModel $input
     */
    private function onInCallingMissed(DynamicModel $input)
    {
        Yii::info('Notification::InCallingMissed ' . var_export($input->getAttributes(), true));
        return true;
    }

}