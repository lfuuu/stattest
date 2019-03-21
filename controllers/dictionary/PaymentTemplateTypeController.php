<?php

namespace app\controllers\dictionary;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\document\PaymentTemplateType;
use DateTime;
use DateTimeZone;
use Yii;
use app\classes\BaseController;
use yii\base\InvalidArgumentException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

class PaymentTemplateTypeController extends BaseController
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
                        'actions' => ['index'],
                        'roles' => ['dictionary.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['edit', 'toggle-enable'],
                        'roles' => ['dictionary.public-site'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => PaymentTemplateType::find(),
            'sort' => false,
            'pagination' => false,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Редактирование типа
     *
     * @param int $id
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionEdit($id = 0)
    {
        try {
            if ($id) {
                if (!($model = PaymentTemplateType::findOne(['id' => $id]))) {
                    throw new InvalidArgumentException('Неверный тип');
                }

                /** @var PaymentTemplateType $model */
                $model->loadDefaultValues();
            } else {
                $model = new PaymentTemplateType;
                $model->created_at = new DateTime('now', new DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW));
            }

            if ($model->load(Yii::$app->request->post())) {
                if (!$model->save()) {
                    throw new ModelValidationException($model);
                }

                return $this->redirect('/dictionary/payment-template-type');
            }
        } catch (\Exception $e) {
            \Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->render('form', [
            'model' => $model,
        ]);
    }

    /**
     * Включение/выключение типа
     *
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionToggleEnable($id = 0)
    {
        try {
            if (!($model = PaymentTemplateType::findOne(['id' => $id]))) {
                throw new InvalidArgumentException('Неверный тип');
            }

            /** @var PaymentTemplateType $model */
            $model->is_enabled = !$model->is_enabled;
            if (!$model->save()) {
                throw new ModelValidationException($model);
            }
        } catch (\Exception $e) {
            \Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->redirect('/dictionary/payment-template-type');
    }

}