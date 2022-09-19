<?php

namespace app\controllers\dictionary\voip;

use app\classes\Html;
use app\exceptions\ModelValidationException;
use app\models\filter\voip\SourceFilter;
use app\models\voip\Source;
use Exception;
use http\Url;
use Throwable;
use Yii;
use app\classes\BaseController;
use yii\base\InvalidParamException;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use app\models\filter\PriceLevelFilter;
use app\models\PriceLevel;
use app\models\ClientAccount;

class SourceController extends BaseController
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
                        'actions' => ['add', 'delete', 'edit'],
                        'roles' => ['dictionary.country'],
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
        $filterModel = new SourceFilter();
        $get = Yii::$app->request->get();
        $filterModel->load($get);

        return $this->render('grid', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function actionAdd()
    {
        $model = new Source();

        try {
            if ($model->load(Yii::$app->request->post())) {
                if (!$model->save()) {
                    throw new ModelValidationException($model);
                }
                return $this->redirect('/dictionary/voip/source');
            }
        } catch (Exception $e) {
            Yii::$app->session->setFlash('error', $this->checkError($e->getMessage(),  $model->code, 'Добавить Источник с таким кодом нельзя. Он уже существует', true));
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * Редактирование
     *
     * @param string $code
     * @return string
     * @throws Exception
     */
    public function actionEdit(string $code)
    {
        $model = Source::findOne(['code' => $code]);

        try {
            if (!$model) {
                throw new \LogicException('Источник не найден');
            }

            if ($model->load(Yii::$app->request->post())) {
                if (!$model->save()) {
                    throw new ModelValidationException($model);
                }
                return $this->redirect('/dictionary/voip/source');
            }
        } catch (Exception $e) {
            Yii::$app->session->setFlash('error', $this->checkError($e->getMessage(), $code, 'Изменить с таким кодом'));
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление
     *
     * @param string $code
     * @throws Exception
     * @throws Throwable
     */
    public function actionDelete($code)
    {
        $model = Source::findOne(['code' => $code]);

        try {
            if (!$model) {
                throw new \LogicException('Источник не найден');
            }

            if (!$model->delete()) {
                throw new ModelValidationException($model);
            }
            Yii::$app->session->addFlash('success', 'Успешно удалено');
        } catch (Exception $e) {
            Yii::$app->session->setFlash('error', $this->checkError($e->getMessage(), $code, 'Удалить'));
        }

        return $this->redirect('/dictionary/voip/source');
    }

    private function checkError($message, $code, $prefix, $isReplace = false)
    {
        if (stripos($message, 'SQLSTATE[23000') !== false) {
            $message = $isReplace ?
                $prefix :
                $prefix . ' Источник нельзя, за ним закреплены ' . Html::a('номера',\yii\helpers\Url::to([
                    '/voip/number',
                    'NumberFilter' => ['source' => $code]
                ]));
        }

        return $message;
    }
}
