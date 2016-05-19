<?php

namespace app\controllers\message;

use Yii;
use yii\data\ActiveDataProvider;
use app\classes\BaseController;
use app\classes\Assert;
use app\models\message\Template;
use app\models\message\TemplateContent;
use app\forms\message\TemplateContentForm;

class TemplateController extends BaseController
{

    /**
     * @return string
     */
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

    /**
     * @param int $id
     * @return string|\yii\web\Response
     */
    public function actionEdit($id = 0)
    {
        $model = new Template;
        if (($record = Template::findOne($id)) instanceof Template) {
            $model = $record;
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $templateContentData = Yii::$app->request->post('TemplateContent');

            foreach ($templateContentData as $templateContentKey => $templateContentInput) {
                list($countryId,, $languageCode, $contentType) = explode('_', $templateContentKey);
                $templateContent = $model->getTemplateContent($countryId, $languageCode, $contentType);

                if ($templateContent->load($templateContentData, $templateContent->formNameKey()) && $templateContent->validate()) {
                    $templateContent->save();
                }
            }

            Yii::$app->session->setFlash('success', 'Данные успешно сохранены');
            return $this->redirect(['message/template/edit', 'id' => $model->id]);
        }

        return $this->render('form', [
            'model' => $model,
        ]);
    }

    /**
     * @param int $id
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionDelete($id)
    {
        $template = Template::findOne($id);
        Assert::isObject($template);

        $template->delete();

        return $this->redirect(['message/template']);
    }

    /**
     * @param int $countryId
     * @param int $templateId
     * @param string $langCode
     */
    public function actionEmailTemplateContent($countryId, $templateId, $langCode)
    {
        /** @var TemplateContent $templateContent */
        $templateContent = TemplateContent::findOne([
            'country_id' => $countryId,
            'template_id' => $templateId,
            'lang_code' => $langCode,
            'type' => 'email'
        ]);

        if (!is_null($templateContent)) {
            $templateContent->mediaManager->getContent($templateContent);
        }
    }

}
