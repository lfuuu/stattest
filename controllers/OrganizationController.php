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

    public function actionIndex()
    {
        return $this->render('list', [
            'organizations' => Organization::dao()->getCompleteList()
        ]);
    }

    public function actionAdd()
    {
        $model = new OrganizationForm;
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect(['edit', 'id' => $model->organization_id, 'date' => $model->actual_from]);
        }

        return $this->render('form', [
            'model' => $model,
            'title' => $model::NEW_TITLE,
        ]);
    }

    public function actionEdit($id, $date)
    {
        $record = Organization::findOne(['organization_id' => $id, 'actual_from' => $date]);

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

    public function actionDuplicate($id, $date)
    {
        $organization = Organization::find()->byId($id)->actual($date)->one();

        if (!$organization instanceof Organization)
            $organization = Organization::find()->byId($id)->orderBy('actual_from DESC')->one();

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