<?php
/**
 * Универсальные тарифы
 */

namespace app\controllers\uu;

use app\classes\BaseController;
use app\classes\uu\filter\TariffFilter;
use app\classes\uu\forms\TariffEditForm;
use app\classes\uu\forms\TariffAddForm;
use app\classes\uu\model\ServiceType;
use Yii;
use yii\filters\AccessControl;


class TariffController extends BaseController
{
    /**
     * Права доступа
     * @return []
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
                        'actions' => ['new', 'edit'],
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
     */
    public function actionIndex($serviceTypeId = ServiceType::ID_VPBX)
    {
        $filterModel = new TariffFilter($serviceTypeId);
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Создать
     *
     * @param $serviceTypeId
     * @return string
     */
    public function actionNew($serviceTypeId)
    {
        /** @var TariffAddForm $formModel */
        $formModel = new TariffAddForm([
            'serviceTypeId' => $serviceTypeId
        ]);

        if ($formModel->isSaved) {
            return $this->redirect([
                'edit',
                'id' => $formModel->id,
            ]);
        } else {
            return $this->render('edit', [
                'formModel' => $formModel,
            ]);
        }
    }

    /**
     * Редактировать
     *
     * @param $id
     * @return string
     */
    public function actionEdit($id)
    {
        try {
            /** @var TariffEditForm $formModel */
            $formModel = new TariffEditForm([
                'id' => $id
            ]);
        } catch (\InvalidArgumentException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());

            return $this->render('//layouts/empty', [
                'content' => '',
            ]);
        }

        if ($formModel->isSaved) {
            return $this->redirect([
                'edit',
                'id' => $formModel->id,
            ]);
        } else {
            return $this->render('edit', [
                'formModel' => $formModel,
            ]);
        }
    }
}