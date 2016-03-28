<?php
/**
 * Универсальные услуги
 */

namespace app\controllers\uu;

use app\classes\BaseController;
use app\classes\uu\filter\AccountTariffFilter;
use app\classes\uu\forms\AccountTariffFormEdit;
use app\classes\uu\forms\AccountTariffFormNew;
use app\classes\uu\model\ServiceType;
use Yii;
use yii\filters\AccessControl;

class AccounttariffController extends BaseController
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
     * Дефолтный обработчик
     * Список
     *
     * @param int $serviceTypeId
     * @return string
     */
    public function actionIndex($serviceTypeId = ServiceType::ID_VPBX)
    {
        $filterModel = new AccountTariffFilter($serviceTypeId);
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Создать
     *
     * @param int $serviceTypeId
     * @return string
     */
    public function actionNew($serviceTypeId)
    {
        $form = new AccountTariffFormNew([
            'serviceTypeId' => $serviceTypeId,
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
     * @param int $id
     * @return string
     */
    public function actionEdit($id)
    {
        $form = new AccountTariffFormEdit([
            'id' => $id,
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