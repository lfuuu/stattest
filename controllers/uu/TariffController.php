<?php
/**
 * Универсальные тарифы
 */

namespace app\controllers\uu;

use app\classes\BaseController;
use app\classes\uu\filter\TariffFilter;
use app\classes\uu\forms\TariffFormEdit;
use app\classes\uu\forms\TariffFormNew;
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
        /** @var TariffFormNew $form */
        $form = new TariffFormNew([
            'serviceTypeId' => $serviceTypeId
        ]);

        if ($form->isSaved) {
            return $this->redirect([
                'edit',
                'id' => $form->id,
            ]);
        } else {
            return $this->render('edit', [
                'formModel' => $form,
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
        /** @var TariffFormEdit $form */
        $form = new TariffFormEdit([
            'id' => $id
        ]);

        if ($form->isSaved) {
            return $this->redirect([
                'edit',
                'id' => $form->id,
            ]);
        } else {
            return $this->render('edit', [
                'formModel' => $form,
            ]);
        }
    }
}