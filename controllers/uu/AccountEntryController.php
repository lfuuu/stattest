<?php
/**
 * Проводки
 */

namespace app\controllers\uu;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\classes\uu\filter\AccountEntryFilter;
use Yii;
use yii\filters\AccessControl;

class AccountEntryController extends BaseController
{
    // Установить юзерские фильтры + добавить фильтр по клиенту, если он есть
    use AddClientAccountFilterTraits;

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
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new AccountEntryFilter();
        $this->addClientAccountFilter($filterModel);

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }
}