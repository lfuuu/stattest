<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use app\exceptions\FormValidationException;
use app\classes\BaseController;
use app\models\TagsResource;

class TagsController extends BaseController
{

    /**
     * @return array
     * @throws BadRequestHttpException
     * @throws FormValidationException
     */
    public function actionApply()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException;
        }

        $tags = new TagsResource;
        if ($tags->load(Yii::$app->request->post(), '') && $tags->validate() && $tags->saveAll()) {
            return ['response' => 'success'];
        }

        throw new FormValidationException($tags);
    }

    /**
     * @param string $resource
     * @return []
     */
    public function actionLoadList($resource)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'results' => array_map(
                function($row) {
                    return ['id' => $row['name'], 'text' => $row['name']];
                },
                TagsResource::getTagList($resource)
            )
        ];
    }

}