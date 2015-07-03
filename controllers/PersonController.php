<?php

namespace app\controllers;

use Yii;
use app\classes\Assert;
use app\classes\BaseController;
use yii\data\ActiveDataProvider;
use app\models\Person;
use app\forms\person\PersonForm;
use yii\web\Response;

class PersonController extends BaseController
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
        $model = new PersonForm;
        $dataProvider = new ActiveDataProvider([
            'query' => Person::find(),
            'sort' => [
                'attributes' => [
                    'id',
                    'name_nominative',
                ],
                'defaultOrder' => [
                    'name_nominative' => SORT_ASC,
                ],
            ],

        ]);

        return $this->render('grid', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAdd()
    {
        $model = new PersonForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('/person');
        }

        return $this->render('edit', [
            'model' => $model
        ]);
    }

    public function actionEdit($id)
    {
        $person = Person::findOne($id);

        Assert::isObject($person);

        $model = new PersonForm;
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save($person)) {
            $this->redirect('/person');
        }

        $model->setAttributes($person->getAttributes(), false);
        return $this->render('edit', [
            'model' => $model,
            'person' => $person,
        ]);
    }

    public function actionDelete($id)
    {
        $person = Person::findOne($id);
        Assert::isObject($person);

        $model = new PersonForm;
        $model->delete($person);

        $this->redirect('/person');
    }

}