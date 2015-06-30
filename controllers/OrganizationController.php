<?php

namespace app\controllers;

use Yii;
use app\classes\Assert;
use app\classes\BaseController;
use app\models\Organization;
use app\forms\organization\OrganizationForm;
use yii\helpers\ArrayHelper;

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
            'records' => Organization::find()->actual()->all()
        ]);
    }

    public function actionAdd()
    {
        $model = new OrganizationForm;
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect(['edit', 'id' => $model->id]);
        }

        return $this->render('form', [
            'model' => $model,
            'frm_header' => $model::NEW_TITLE,
        ]);
    }

    public function actionEdit($id)
    {
        $record = Organization::findOne($id);

        Assert::isObject($record);

        $model = new OrganizationForm;
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save($record)) {
            $this->redirect(['edit', 'id' => $model->id]);
        }

        $model->setAttributes($record->getAttributes(), false);
        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    public function actionDuplicate($firma)
    {
        $model = new OrganizationForm;
        $model->duplicate(Organization::find()->where(['firma' => $firma])->actual()->one());

        return $this->render('form', [
            'model' => $model,
            'frm_header' => $model::EDIT_TITLE,
            'mode' => 'duplicate',
        ]);
    }
}