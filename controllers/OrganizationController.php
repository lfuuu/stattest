<?php

namespace app\controllers;

use Yii;
use app\classes\Assert;
use app\classes\BaseController;
use app\models\Organization;
use app\forms\organization\OrganizationForm;

class OrganizationController extends BaseController
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        /*
        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['add'],
                'roles' => ['person.edit'],
            ],
            [
                'allow' => true,
                'actions' => ['delete'],
                'roles' => ['person.delete'],
            ],
            [
                'allow' => false,
            ],
        ];
        */
        return $behaviors;
    }

    public function actionIndex()
    {
        return $this->render('list', [
            'records' => Organization::dao()->getCompleteList()
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