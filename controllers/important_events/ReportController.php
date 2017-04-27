<?php

namespace app\controllers\important_events;

use app\classes\Assert;
use app\classes\BaseController;
use app\classes\DynamicModel;
use app\exceptions\ModelValidationException;
use app\models\important_events\ImportantEvents;
use Yii;
use yii\base\InvalidParamException;
use yii\db\StaleObjectException;
use yii\web\Response;

class ReportController extends BaseController
{

    /**
     * @return string
     * @throws InvalidParamException
     */
    public function actionIndex()
    {
        $searchModel = new ImportantEvents;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
        ]);
    }

    /**
     * @return array
     * @throws ModelValidationException
     * @throws \yii\base\InvalidConfigException
     * @throws StaleObjectException
     * @throws \Exception
     */
    public function actionSetComment()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $data = DynamicModel::validateData(Yii::$app->request->post(), [
            ['id', 'integer'],
            ['id', 'required'],
            ['comment', 'string'],
        ]);

        if ($data->hasErrors()) {
            throw new ModelValidationException($data);
        }

        /** @var ImportantEvents $event */
        $event = ImportantEvents::findOne($data->id);
        Assert::isObject($event);

        $event->comment = $data->comment;
        $event->update($runValidation = false);

        return ['response' => 'success'];
    }

}