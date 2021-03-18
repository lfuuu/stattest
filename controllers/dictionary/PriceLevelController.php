<?php

namespace app\controllers\dictionary;

use app\exceptions\ModelValidationException;
use Exception;
use Throwable;
use Yii;
use app\classes\BaseController;
use yii\base\InvalidParamException;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use app\models\filter\PriceLevelFilter;
use app\models\PriceLevel;
use app\models\ClientAccount;

class PriceLevelController extends BaseController
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
        $filterModel = new PriceLevelFilter();
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
        /** @var PriceLevel $model */
        $model = new PriceLevel();

        try {
            if ($model->load(Yii::$app->request->post())) {
                if (!$model->save()) {
                    throw new ModelValidationException($model);
                }
                return $this->redirect('/dictionary/price-level');
            }
        } catch (Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * Редактирование уровня цен
     *
     * @param int $id id уровня цен
     * @return string
     * @throws Exception
     */
    public function actionEdit($id)
    {
        $model = PriceLevel::findOne(['id' => $id]);

        if (!$model) {
            throw new Exception('Уровень цен не найден');
        }

        try {
            if ($model->load(Yii::$app->request->post())) {
                if (!$model->save()) {
                    throw new ModelValidationException($model);
                }
                return $this->redirect('/dictionary/price-level');
            }
        } catch (Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление точки входа
     *
     * @param int $id id точки входа
     * @throws Exception
     * @throws Throwable
     */
    public function actionDelete($id)
    {
        $model = PriceLevel::findOne(['id' => $id]);
        if (!$model) {
            throw new InvalidParamException('Уровень цен не найден');
        }

        try {
            if (!$model->delete()) {
                throw new ModelValidationException($model);
            }
            Yii::$app->session->addFlash('success', 'Успешно удалено');
        } catch (Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect('/dictionary/price-level');
    }
}
