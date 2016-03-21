<?php

namespace app\controllers\message;

use Yii;
use yii\web\UploadedFile;
use yii\data\ActiveDataProvider;
use app\classes\BaseController;
use app\classes\Assert;
use app\models\message\Template;
use app\models\message\TemplateContent;

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
            return $this->redirect(['message/template/edit', 'id' => $model->id]);
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

    /**
     * @return \yii\web\Response
     */
    public function actionEditTemplateContent()
    {
        $model = new TemplateContent;
        $content = null;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $content = TemplateContent::findOne([
                'template_id' => $model->template_id,
                'type' => $model->type,
                'lang_code' => $model->lang_code,
            ]);

            if (is_null($content)) {
                $content = $model;
            }

            if (($file = UploadedFile::getInstance($content, 'filename')) !== null) {
                $content->mediaManager->addFile($file);
            }

            if ($content->save()) {
                Yii::$app->session->setFlash('success', 'Данные успешно сохранены');
            }
        }

        return $this->redirect(['message/template/edit', 'id' => $content->template_id]);
    }

}