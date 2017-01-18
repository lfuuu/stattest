<?php

namespace app\modules\notifier\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use app\classes\BaseController;
use app\classes\Assert;
use app\modules\notifier\models\templates\Template;
use app\modules\notifier\models\templates\TemplateContent;

class EmailTemplatesController extends BaseController
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

        return $this->render('grid',
            [
                'dataProvider' => $dataProvider,
            ]
        );
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
                list($countryId,, $languageCode, $contentType) = explode(':', $templateContentKey);
                $templateContent = $model->getTemplateContent($countryId, $languageCode, $contentType);

                if ($templateContent->load($templateContentData, $templateContent->formNameKey()) && $templateContent->validate()) {
                    $templateContent->save();
                }
            }

            Yii::$app->session->setFlash('success', 'Данные успешно сохранены');
            return $this->redirect(['/notifier/email-templates/edit', 'id' => $model->id]);
        }

        return $this->render('form',
            [
                'model' => $model,
            ]
        );
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

        return $this->redirect(['/notifier/email-templates']);
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

    /**
     * @param int $countryId
     * @param int $templateId
     * @param string $langCode
     * @throws \yii\base\Exception
     * @throws \yii\base\ExitException
     * @throws \yii\web\HttpException
     */
    public function actionDownloadTemplate($countryId, $templateId, $langCode)
    {
        /** @var TemplateContent $templateContent */
        $templateContent = TemplateContent::findOne([
            'country_id' => $countryId,
            'template_id' => $templateId,
            'lang_code' => $langCode,
            'type' => 'email',
        ]);

        Assert::isObject($templateContent);

        $templateContent->mediaManager->getContent($templateContent, $isDownload = true);
    }

}
