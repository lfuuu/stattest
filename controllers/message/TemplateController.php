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
        $dataKey = (new TemplateContent)->formName();
        $data = Yii::$app->request->post($dataKey);

        for ($i=0, $s=count($data['type']); $i<$s; $i++) {
            $content =
                TemplateContent::find()
                    ->where([
                        'template_id' => $template_id,
                        'type' => $data['type'][$i],
                        'lang_code' => $data['lang_code'][$i],
                    ])
                    ->one();
            if (!($content instanceof TemplateContent)) {
                $content = new TemplateContent;
            }
            $content->template_id = $template_id;
            $content->type = $data['type'][$i];
            $content->lang_code = $data['lang_code'][$i];
            if (isset($data['title'][$i])) {
                $content->title = $data['title'][$i];
            }
            $content->content = $data['content'][$i];
            if ($content->validate()) {
                $content->save();
            }
        }

        Yii::$app->session->setFlash('success', 'Данные успешно сохранены');

        return $this->redirect(['message/template/edit', 'id' => $template_id]);
    }

}