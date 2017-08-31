<?php
/**
 * Универсальные тарифы
 */

namespace app\modules\uu\controllers;

use app\classes\Assert;
use app\classes\BaseController;
use app\modules\uu\filter\TariffFilter;
use app\modules\uu\forms\TariffAddForm;
use app\modules\uu\forms\TariffEditForm;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use Yii;
use yii\filters\AccessControl;


class TariffController extends BaseController
{
    const EDITABLE_NONE = 0;
    const EDITABLE_LIGHT = 1;
    const EDITABLE_FULL = 2;

    /**
     * Права доступа
     *
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
                        'actions' => ['index'],
                        'roles' => ['tarifs.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['new', 'edit', 'edit-by-tariff-period'],
                        'roles' => ['tarifs.edit'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Список
     *
     * @param int $serviceTypeId
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex($serviceTypeId = ServiceType::ID_VPBX)
    {
        $filterModel = new TariffFilter($serviceTypeId);
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', ['filterModel' => $filterModel]);
    }

    /**
     * Создать
     *
     * @param int $serviceTypeId
     * @param int $countryId
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionNew($serviceTypeId, $countryId = null)
    {
        /** @var TariffAddForm $formModel */
        $formModel = new TariffAddForm(['serviceTypeId' => $serviceTypeId, 'countryId' => $countryId]);

        // сообщение об ошибке
        if ($formModel->validateErrors) {
            Yii::$app->session->setFlash('error', $formModel->validateErrors);
        }

        if ($formModel->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was created successfully'));
            return $this->redirect(['edit', 'id' => $formModel->id]);
        }

        return $this->render('edit', ['formModel' => $formModel]);
    }

    /**
     * Редактировать
     *
     * @param int $id
     * @param int $countryId
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionEdit($id, $countryId = null)
    {
        try {
            /** @var TariffEditForm $formModel */
            $formModel = new TariffEditForm(['id' => $id, 'countryId' => $countryId]);
        } catch (\InvalidArgumentException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());

            return $this->render('//layouts/empty', ['content' => '']);
        }

        // сообщение об ошибке
        if ($formModel->validateErrors) {
            Yii::$app->session->setFlash('error', $formModel->validateErrors);
        }

        if ($formModel->isSaved) {
            if ($formModel->id) {
                Yii::$app->session->setFlash('success', Yii::t('common', 'The object was saved successfully'));
                return $this->redirect(['edit', 'id' => $formModel->id]);
            }

            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was dropped successfully'));
            return $this->redirect(['index', 'serviceTypeId' => $formModel->tariff->service_type_id]);
        }

        return $this->render('edit', ['formModel' => $formModel, 'clientAccount' => $this->getFixClient()]);
    }

    /**
     * @param int $tariffPeriodId
     */
    public function actionEditByTariffPeriod($tariffPeriodId)
    {
        /** @var TariffPeriod $tariffPeriod */
        $tariffPeriod = TariffPeriod::findOne(['id' => $tariffPeriodId]);
        Assert::isObject($tariffPeriod);

        $this->redirect(['edit', 'id' => $tariffPeriod->tariff_id]);
    }
}