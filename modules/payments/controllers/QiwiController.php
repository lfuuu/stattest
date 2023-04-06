<?php

namespace app\modules\payments\controllers;

use app\classes\BaseController;
use app\models\User;
use app\modules\payments\classes\QiwiExitObject;
use app\modules\payments\forms\QiwiForm;
use kartik\base\Config;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\auth\HttpBasicAuth;
use yii\web\Response;

/**
 * Qiwi payments processor
 */
class QiwiController extends BaseController
{

    private $_moduleConfig = null;

    public function init()
    {
        parent::init();

        $this->_moduleConfig = Config::getModule('payments');
    }

    public function behaviors()
    {
        return [
            'basicAuth' => [
                'class' => HttpBasicAuth::class,
                'auth' => function ($user, $password) {

                    $params = $this->_moduleConfig->params['QiwiBasicAuth'];

                    if (!$params['user'] || !$params['password']) {
                        throw new InvalidConfigException('Qiwi access parameters is not set');
                    }

                    return
                        $user == $params['user']
                        && $password == $params['password'] ?
                            new User() :
                            null;
                }
            ]
        ];
    }

    /**
     * Основная точка входа обработки запросов от Qiwi
     *
     * @return string
     * @throws QiwiExitObject
     */
    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_XML;
        $form = new QiwiForm();

        $qiwiTransaction = [];

        try {
            if (!$form->load(Yii::$app->request->get(), '')) {
                throw new QiwiExitObject(QiwiExitObject::ERROR_UNKNOWN, 'data not load');
            }

            $qiwiTransaction = ['osmp_txn_id' => $form->txn_id]; // всегда возвращать Id платежа
            if ($form->command == QiwiForm::COMMAND_PAY) {
                $qiwiTransaction['sum'] = $form->sum; // при оплате - возвращать и сумму
            }

            if (!$form->validate()) {
                $errors = $form->getFirstErrors();
                $e = reset($errors);
                throw new QiwiExitObject($e);
            }

            $result = $form->command == QiwiForm::COMMAND_PAY ? $form->doPay() : $form->doCheck();

        } catch (QiwiExitObject $exitObject) {
            $result = $exitObject;
        } catch (\Exception $e) {
            \Yii::error($e, 'payments_qiwi');
            $result = new QiwiExitObject(QiwiExitObject::ERROR_SERVICE_ERROR, $e->getMessage());
        }

        return [
                'result' => $result->getCode(),
                'comment' => $result->getMessage(),
            ] + $qiwiTransaction + $result->getData();
    }
}
