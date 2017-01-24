<?php

namespace app\controllers\api;

use Yii;
use app\classes\validators\AccountIdValidator;
use app\classes\validators\TicketIdValidator;
use app\classes\ApiController;
use app\classes\DynamicModel;
use app\exceptions\ModelValidationException;
use app\forms\support\SubmitTicketCommentForm;
use app\forms\support\SubmitTicketForm;
use app\forms\support\TicketListForm;
use app\models\support\Ticket;
use app\models\support\TicketComment;
use app\models\Trouble;

class TicketController extends ApiController
{
    /**
     * @SWG\Definition(
     *   definition="ticket",
     *   type="object",
     *   required={"id","user_id","subject","status","is_with_new_comment","department","created_at","updated_at"},
     *   @SWG\Property(property="id",type="integer",description="Идентификатор тикета"),
     *   @SWG\Property(property="user_id",type="integer",description="Ответственный менеджер"),
     *   @SWG\Property(property="subject",type="string",description="Тема тикета"),
     *   @SWG\Property(property="status",type="integer",description="Статус",enum={"open|done|closed"}),
     *   @SWG\Property(property="is_with_new_comment",type="integer",description="Есть ли новые комментарии к тикету"),
     *   @SWG\Property(property="department",type="integer",description="Отдел",enum={"sales|accounting|technical"}),
     *   @SWG\Property(property="created_at",type="date",description="Дата создания"),
     *   @SWG\Property(property="updated_at",type="date",description="Дата изменения")
     * ),
     * @SWG\Post(
     *   tags={"Работа с тикетами"},
     *   path="/ticket/list/",
     *   summary="Список тикетов",
     *   operationId="Список тикетов",
     *   @SWG\Parameter(name="client_account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Parameter(name="status",type="string",description="статус тикета",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="список тикетов",
     *     @SWG\Definition(
     *       type="array",
     *       @SWG\Items(
     *         ref="#/definitions/ticket"
     *       )
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    public function actionList()
    {
        $model = new TicketListForm();
        $model->load(Yii::$app->request->bodyParams, '');
        if ($model->validate()) {
            $data = [];
            foreach ($model->spawnFilteredQuery()->all() as $ticket) {
                $data[] = $ticket->toArray();
            }
            return $data;
        } else {
            throw new ModelValidationException($model);
        }
    }

    /**
     * @SWG\Definition(
     *   definition="ticket_comment",
     *   type="object",
     *   required={"id","ticket_id","user_id","text","created_at"},
     *   @SWG\Property(property="id",type="integer",description="Идентификатор комментария"),
     *   @SWG\Property(property="ticket_id",type="integer",description="Идентификатор тикета"),
     *   @SWG\Property(property="user_id",type="string",description="Идентификатор сотрудника техподдержки"),
     *   @SWG\Property(property="text",type="string",description="Текст комментария"),
     *   @SWG\Property(property="created_at",type="date",description="Дата создания комментария")
     * ),
     * @SWG\Definition(
     *   definition="ticket_ex",
     *   type="object",
     *   required={"id","user_id","subject","status","is_with_new_comment","department","created_at","updated_at","comments"},
     *   @SWG\Property(property="id",type="integer",description="Идентификатор тикета"),
     *   @SWG\Property(property="user_id",type="integer",description="Ответственный менеджер"),
     *   @SWG\Property(property="subject",type="string",description="Тема тикета"),
     *   @SWG\Property(property="status",type="integer",description="Статус",enum={"open|done|closed"}),
     *   @SWG\Property(property="is_with_new_comment",type="integer",description="Есть ли новые комментарии к тикету"),
     *   @SWG\Property(property="department",type="integer",description="Отдел",enum={"sales|accounting|technical"}),
     *   @SWG\Property(property="created_at",type="date",description="Дата создания"),
     *   @SWG\Property(property="updated_at",type="date",description="Дата изменения"),
     *   @SWG\Property(property="comments",type="array",description="Комментарии",@SWG\Items(ref="#/definitions/ticket_comment")),
     * ),
     * @SWG\Post(
     *   tags={"Работа с тикетами"},
     *   path="/ticket/details/",
     *   summary="Информация о тикете",
     *   operationId="Информация о тикете",
     *   @SWG\Parameter(name="client_account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Parameter(name="ticket_id",type="integer",description="идентификатор тикета",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="тикет",
     *     @SWG\Definition(
     *       ref="#/definitions/ticket_ex"
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    public function actionDetails()
    {
        $model = DynamicModel::validateData(
            Yii::$app->request->bodyParams,
            [
                ['client_account_id', AccountIdValidator::className()],
                ['ticket_id', TicketIdValidator::className()],
                [['ticket_id'], 'required'],
            ]
        );

        if (!$model->hasErrors()) {
            $ticket =
                Ticket::find()
                    ->andWhere(['id' => $model->ticket_id])
                    ->andWhere(['client_account_id' => $model->client_account_id])
                    ->one();
            if ($ticket !== null) {
                $ticket = $ticket->toArray();
                $ticket['comments'] = [];
                $ticketComments = TicketComment::find()
                    ->andWhere(['ticket_id' => $model->ticket_id])
                    ->orderBy('created_at')
                    ->all();
                if ($ticketComments) {
                    foreach ($ticketComments as $ticketComment) {
                        $ticket["comments"][] = $ticketComment->toArray();
                    }
                }
            }
            return $ticket;
        } else {
            throw new ModelValidationException($model);
        }
    }

    /**
     * @SWG\Post(
     *   tags={"Работа с тикетами"},
     *   path="/ticket/create/",
     *   summary="Создание тикета",
     *   operationId="Создание тикета",
     *   @SWG\Parameter(name="user_id",type="integer",description="Ответственный менеджер",in="formData"),
     *   @SWG\Parameter(name="subject",type="string",description="Тема тикета",in="formData"),
     *   @SWG\Parameter(name="status",type="integer",description="Статус",enum={"open|done|closed"},in="formData"),
     *   @SWG\Parameter(name="is_with_new_comment",type="integer",description="Есть ли новые комментарии к тикету",in="formData"),
     *   @SWG\Parameter(name="department",type="integer",description="Отдел",enum={"sales|accounting|technical"},in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="идентификатор созданного тикета",
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    public function actionCreate()
    {
        $model = new SubmitTicketForm();
        $model->load(Yii::$app->request->bodyParams, '');
        $model->author = Trouble::DEDAULT_API_AUTHOR;
        if ($model->save()) {
            return ['ticket_id' => $model->id];
        } else {
            throw new ModelValidationException($model);
        }
    }

    /**
     * @SWG\Post(
     *   tags={"Работа с тикетами"},
     *   path="/ticket/comment/",
     *   summary="Создание комментария к тикету",
     *   operationId="Создание комментария к тикету",
     *   @SWG\Parameter(name="ticket_id",type="integer",description="Идентификатор тикета",in="formData"),
     *   @SWG\Parameter(name="user_id",type="string",description="Идентификатор сотрудника техподдержки",in="formData"),
     *   @SWG\Parameter(name="text",type="string",description="Текст комментария",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="идентификаторы",
     *     @SWG\Definition(
     *       @SWG\Property(property="ticket_id",type="integer",description="Идентификатор тикета"),
     *       @SWG\Property(property="comment_id",type="daintegerte",description="Идентификатор комментария")
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    public function actionComment()
    {
        $model = new SubmitTicketCommentForm();
        $model->load(Yii::$app->request->bodyParams, '');
        if ($model->save()) {
            return ['ticket_id' => $model->ticket_id, 'comment_id' => $model->id];
        } else {
            throw new ModelValidationException($model);
        }
    }

    /**
     * @SWG\Post(
     *   tags={"Работа с тикетами"},
     *   path="/ticket/set-read/",
     *   summary="Отметить комментарии в тикете как прочитанные",
     *   operationId="Отметить комментарии в тикете как прочитанные",
     *   @SWG\Parameter(name="client_account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Parameter(name="ticket_id",type="integer",description="идентификатор тикета",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="тикет",
     *     @SWG\Definition(
     *       ref="#/definitions/ticket"
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    public function actionSetRead()
    {
        $model = DynamicModel::validateData(
            Yii::$app->request->bodyParams,
            [
                ['client_account_id', AccountIdValidator::className()],
                ['ticket_id', TicketIdValidator::className()],
                [['ticket_id'], 'required'],
            ]
        );

        if (!$model->hasErrors()) {
            $ticket =
                Ticket::find()
                    ->andWhere(['id' => $model->ticket_id])
                    ->andWhere(['client_account_id' => $model->client_account_id])
                    ->one();

            if ($ticket->is_with_new_comment) {
                $ticket->is_with_new_comment = 0;
                $ticket->save();
            }
            return $ticket->toArray();
        } else {
            throw new ModelValidationException($model);
        }
    }

}
