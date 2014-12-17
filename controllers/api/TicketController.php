<?php

namespace app\controllers\api;

use app\classes\validators\AccountIdValidator;
use app\classes\validators\TicketIdValidator;
use app\exceptions\FormValidationException;
use app\forms\support\SubmitTicketCommentForm;
use app\forms\support\SubmitTicketForm;
use app\forms\support\TicketListForm;
use app\models\support\Ticket;
use app\models\support\TicketComment;
use Yii;
use app\classes\ApiController;
use app\classes\DynamicModel;

class TicketController extends ApiController
{
    public function actionList()
    {
        $model = new TicketListForm();
        $model->load(Yii::$app->request->bodyParams, '');
        if ($model->validate()) {
            return
              $model->spawnFilteredQuery()
                ->asArray()
                ->all();
        } else {
            throw new FormValidationException($model);
        }
    }

    public function actionDetails()
    {
        $model = DynamicModel::validateData(
              Yii::$app->request->bodyParams,
              [
                  ['client_account_id', AccountIdValidator::className()],
                  ['ticket_id', TicketIdValidator::className()],
                  [['client_account_id', 'ticket_id'], 'required'],
              ]
          );

        if (!$model->hasErrors()) {
            $ticket =
                Ticket::find()
                    ->andWhere(['id' => $model->ticket_id])
                    ->andWhere(['client_account_id' => $model->client_account_id])
                    ->asArray()
                    ->one();
            if ($ticket !== null) {
                $ticket['comments'] =
                    TicketComment::find()
                        ->andWhere(['ticket_id' => $model->ticket_id])
                        ->orderBy('created_at')
                        ->asArray()
                        ->all();
            }
            return $ticket;
        } else {
            throw new FormValidationException($model);
        }
    }

    public function actionCreate()
    {
        $model = new SubmitTicketForm();
        $model->load(Yii::$app->request->bodyParams, '');
        if ($model->save()) {
            return ['ticket_id' => $model->id];
        } else {
            throw new FormValidationException($model);
        }
    }

    public function actionComment()
    {
        $model = new SubmitTicketCommentForm();
        $model->load(Yii::$app->request->bodyParams, '');
        if ($model->save()) {
            return ['ticket_id' => $model->ticket_id, 'comment_id' => $model->id];
        } else {
            throw new FormValidationException($model);
        }
    }

}
