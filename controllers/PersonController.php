<?php

namespace app\controllers;

use Yii;
use app\classes\Assert;
use app\classes\BaseController;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use app\models\Person;
use app\forms\person\PersonForm;

class PersonController extends BaseController
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
                        'roles' => ['person.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['add', 'edit'],
                        'roles' => ['person.edit'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'roles' => ['person.delete'],
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
        $model = new PersonForm;
        $dataProvider = new ActiveDataProvider([
            'query' => Person::find(),
            'sort' => [
                'attributes' => [
                    'id',
                ],
            ],

        ]);

        return $this->render('grid', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function actionAdd()
    {
        $model = new PersonForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('/person');
        }

        return $this->render('edit', [
            'model' => $model,
            'person' => new Person,
        ]);
    }

    /**
     * @param int $id
     * @return string
     * @throws \Exception
     * @throws \yii\base\Exception
     */
    public function actionEdit($id)
    {
        /** @var Person $person */
        $person = Person::findOne($id);
        Assert::isObject($person);

        $form = new PersonForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save($person)) {
            $this->redirect('/person');
        }

        $form->setAttributes($person->getAttributes(), false);
        return $this->render('edit', [
            'model' => $form,
            'person' => $person,
        ]);
    }

    /**
     * @param int $id
     * @throws \Exception
     * @throws \yii\base\Exception
     */
    public function actionDelete($id)
    {
        $person = Person::findOne($id);
        Assert::isObject($person);

        $form = new PersonForm;
        $form->delete($person);

        $this->redirect('/person');
    }

}