<?php

namespace app\controllers\templates\uu;

use app\forms\templates\uu\PaymentForm;
use app\models\document\PaymentTemplate;
use Yii;
use app\classes\BaseController;
use yii\filters\AccessControl;
use yii\helpers\Url;

class PaymentController extends BaseController
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
                        'actions' => ['get-content', 'download-content', 'set-default', 'delete', 'restore'],
                        'roles' => ['dictionary.public-site'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $id
     * @return string
     * @throws \app\exceptions\ModelValidationException
     */
    public function actionIndex($id = 0)
    {
        $model = new PaymentTemplate();
        $model->load(Yii::$app->request->get());

        $formModel = new PaymentForm($model->type_id);
        $formModel->id = $id;
        if ($paymentTemplate = $formModel->getTemplate()) {
            $model = $paymentTemplate;
        }

        if (Yii::$app->request->isPost) {
            try {
                if ($formModel->save()) {
                    // get new template
                    $model = $formModel->getTemplate();
                }
            } catch (\Exception $e) {
                \Yii::$app->session->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('edit', [
            'model' => $model,
            'formModel' => $formModel,
        ]);
    }

    /**
     * Get content
     *
     * @param int $id
     * @throws \yii\base\ExitException
     */
    public function actionGetContent($id = 0)
    {
        if ($model = PaymentTemplate::findOne(['id' => $id])) {
            Yii::$app->response->content = $model->content;
            Yii::$app->response->send();
            Yii::$app->end(200);
        }
    }

    /**
     * Download content
     *
     * @param int $id
     * @throws \yii\base\ExitException
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionDownloadContent($id = 0)
    {
        if ($model = PaymentTemplate::findOne(['id' => $id])) {
            $fileName = PaymentForm::getFileName(
                $model->type_id,
                $model->country->alpha_3,
                $model->version,
                PaymentForm::TEMPLATE_EXTENSION
            );

            Yii::$app->response->sendContentAsFile($model->content, $fileName);
        }

        Yii::$app->end(200);
    }

    /**
     * Set default
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionSetDefault($id = 0)
    {
        try {
            $id = PaymentForm::setDefault($id);
        } catch (\Exception $e) {
            \Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->redirect(Url::to(['/templates/uu/payment', 'id' => $id]));
    }

    /**
     * Disable template
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionDelete($id = 0)
    {
        try {
            $id = PaymentForm::delete($id);
        } catch (\Exception $e) {
            \Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->redirect(Url::to(['/templates/uu/payment', 'id' => $id]));
    }

    /**
     * Restore template
     *
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionRestore($id = 0)
    {
        try {
            $id = PaymentForm::restore($id);
        } catch (\Exception $e) {
            \Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->redirect(Url::to(['/templates/uu/payment', 'id' => $id]));
    }

}
