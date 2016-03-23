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
     * @param int $templateId
     * @param string $type
     * @param string $langCode
     * @return \yii\web\Response
     */
    public function actionEditTemplateContent($templateId, $type, $langCode)
    {
        /** @var TemplateContent $content */
        $content = TemplateContent::findOne([
            'template_id' => $templateId,
            'type' => $type,
            'lang_code' => $langCode,
        ]);

        if (is_null($content)) {
            $content = new TemplateContent;
            $content->setAttribute('template_id', $templateId);
            $content->setAttribute('type', $type);
            $content->setAttribute('lang_code', $langCode);
        }

        if (($file = UploadedFile::getInstance($content, 'filename')) !== null) {
            $content->mediaManager->addFile($file);
        }

        if ($content->load(Yii::$app->request->post()) && $content->validate() && $content->save()) {
            Yii::$app->session->setFlash('success', 'Данные успешно сохранены');
            return $this->redirect(['message/template/edit', 'id' => $content->template_id]);
        }

        return $this->redirect(['message/template']);
    }

}