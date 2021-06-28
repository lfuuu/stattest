<?php

namespace app\controllers\tariff;

use Exception;
use Throwable;
use app\classes\BaseController;
use app\forms\tariff\DidGroupFormEdit;
use app\forms\tariff\DidGroupFormNew;
use app\models\DidGroup;
use app\models\DidGroupPriceLevel;
use app\models\filter\DidGroupFilter;
use Yii;
use yii\filters\AccessControl;
use app\exceptions\ModelValidationException;
use yii\base\InvalidParamException;

class DidGroupController extends BaseController
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
                        'roles' => ['tarifs.read'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Список
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $filterModel = new DidGroupFilter();
        $filterModel->load(Yii::$app->request->getQueryParams());

        return $this->render('index', ['filterModel' => $filterModel]);
    }

    /**
     * Создать
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionNew()
    {
        /** @var DidGroupFormNew $formModel */
        $formModel = new DidGroupFormNew();
        if ($formModel->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was created successfully'));
            return $this->redirect(['index']);
        }

        return $this->render('edit', ['formModel' => $formModel]);
    }

    /**
     * Редактировать
     *
     * @param int $id
     * @param int $did_group_id
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionEdit($id)
    {
        /** @var DidGroupFormEdit $formModel */
        $formModel = new DidGroupFormEdit(['id' => $id]);
        if (!$formModel) {
            throw new InvalidParamException('DID не найден');
        }

        if ($formModel->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', $formModel->id ? 'The object was saved successfully' : 'The object was dropped successfully'));
            return $this->redirect(['edit', 'id' => $formModel->id]);
        }

        return $this->render('edit', ['formModel' => $formModel]);
    }

    /**
     * @param int $id
     * @throws Throwable
     * @throws Exception
     */
    public function actionDelete($id)
    {
        $didGroup = DidGroup::findOne(['id' => $id]);
        if (!$didGroup) {
            throw new InvalidParamException('DID группы не существует');
        }

        $didGroupPriceLevels = DidGroupPriceLevel::findAll(['did_group_id' => $id]);
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($didGroupPriceLevels as $row) {
                if (!$row->delete()) {
                    throw new ModelValidationException($row);
                }
            }
            if (!$didGroup->delete()) {
                throw new ModelValidationException($didGroup);
            }
            $transaction->commit();
            Yii::$app->session->addFlash('success', 'Успешно удалено');
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect('/tariff/did-group');
    }
}
