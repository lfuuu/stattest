<?php

namespace app\controllers;

use Yii;
use app\classes\Assert;
use app\classes\BaseController;
use yii\filters\AccessControl;
use app\models\Organization;
use app\forms\organization\OrganizationForm;

class OrganizationController extends BaseController
{

    /**
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
                        'roles' => ['organization.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['add', 'edit', 'duplicate'],
                        'roles' => ['organization.edit'],
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
        return $this->render('list', [
            'organizations' => Organization::dao()->getCompleteList()
        ]);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function actionAdd()
    {
        $model = new OrganizationForm;
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect(['edit', 'id' => $model->organization_id, 'date' => $model->actual_from]);
        }

        return $this->render('form', [
            'model' => $model,
            'history' => new Organization,
            'title' => $model::NEW_TITLE,
        ]);
    }

    /**
     * @param int $id
     * @param string $date
     * @return string
     * @throws \Exception
     * @throws \yii\base\Exception
     */
    public function actionEdit($id, $date = '')
    {
        /** @var Organization $record */
        $record = Organization::find()->where(['organization_id' => $id]);
        if ($date) {
            $record->andWhere(['actual_from' => $date]);
        } else {
            $record->orderBy(['actual_from' => SORT_DESC]);
        }
        $record = $record->one();
        Assert::isObject($record);

        $model = new OrganizationForm;
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save($record)) {
            $this->redirect(['edit', 'id' => $model->organization_id, 'date' => $model->actual_from]);
        }

        $history = Organization::find()->byId($record->organization_id)->orderBy('actual_from asc')->all();

        $model->setAttributes($record->getAttributes(), false);
        return $this->render('edit', [
            'model' => $model,
            'history' => $history,
        ]);
    }

    /**
     * @param int $id
     * @param string $date
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionDuplicate($id, $date)
    {
        $organization = Organization::find()->byId($id)->actual($date)->one();

        if (!$organization instanceof Organization) {
            $organization = Organization::find()->byId($id)->orderBy('actual_from DESC')->one();
        }

        Assert::isObject($organization);

        $model = new OrganizationForm;
        $model->duplicate($organization);

        return $this->render('form', [
            'model' => $model,
            'frm_header' => $model::EDIT_TITLE,
            'mode' => 'duplicate',
        ]);
    }
}