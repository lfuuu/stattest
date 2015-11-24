<?php

namespace app\controllers\message;

use app\models\message\TemplateContent;
use Yii;
use yii\data\ActiveDataProvider;
use app\classes\BaseController;
use app\classes\Assert;
use app\models\message\Template;

class TemplateController extends BaseController
{

    public function actionIndex()
    {
        $model = new Template;
        $model->load(Yii::$app->request->getQueryParams());

        $dataProvider = new ActiveDataProvider([
            'query' => $model::find(),
            'sort' => false,
        ]);

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionEdit($id = 0)
    {
        $model = new Template;
        if (($record = Template::findOne($id)) instanceof Template) {
            $model = $record;
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            Yii::$app->session->setFlash('success', 'Данные успешно сохранены');
            return $this->redirect(['message/template']);
        }

        return $this->render('form', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $template = Template::findOne($id);
        Assert::isObject($template);

        $template->delete();

        return $this->redirect(['message/template']);
    }

    public function actionEditTemplateContent($template_id)
    {
        $model = new TemplateContent;
        $model->template_id = $template_id;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            Yii::$app->session->setFlash('success', 'Данные успешно сохранены');
        }

        return $this->redirect(['message/template/edit', 'id' => $template_id]);
    }

}