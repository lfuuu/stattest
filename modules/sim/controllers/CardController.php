<?php

namespace app\modules\sim\controllers;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\modules\sim\filters\CardFilter;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\VirtualCard;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * SIM-карты
 */
class CardController extends BaseController
{
    // Установить юзерские фильтры + добавить фильтр по клиенту, если он есть
    use AddClientAccountFilterTraits;

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['sim.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => [
                            'new', 'edit', 'change-msisdn', 'create-card', 'update-card'
                        ],
                        'roles' => ['sim.write'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Список
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $filterModel = new CardFilter();
        // $this->_addClientAccountFilter($filterModel);
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * @return string
     */
    public function actionNew()
    {
        $originCard = new Card;
        $originCard->is_active = true;
        $originCard->status_id = CardStatus::ID_DEFAULT;

        return $this->render('edit', [
            'originCard' => $originCard,
        ]);
    }

    /**
     * @param  integer $iccid
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionEdit($iccid)
    {
        $request = Yii::$app->request;

        if (!$iccid || !$originCard = Card::findOne(['iccid' => $iccid])) {
            throw new NotFoundHttpException;
        }

        $imsies = $originCard->imsies;
        if ($this->loadFromInput($originCard, $imsies, new Imsi())) {
            return $this->redirect($originCard->getUrl());
        }

        // Получение свободной сим-карты на основе требуемого статуса
        $virtualCard = null;
        $status = (int) $request->post('status');
        if ($status) {
            $virtualCard = VirtualCard::find()
                ->where([
                    'status_id' => $status,
                    'client_account_id' => null,
                    'is_active' => 1,
                ])
                ->one();
        }

        return $this->render('edit', [
            'originCard' => $originCard,
            'virtualCard' => $virtualCard,
        ]);
    }

    /**
     * Метод смены MSISDN на сим-картах
     *
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionChangeMsisdn()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $request = $this->_getAjaxRequest();
            $cardsIccid = $request->post('cards_iccid');
            // Базовые проверки данных, по которым будут загружаться OriginCard и VirtualCard
            if (count($cardsIccid) !== 2 || !isset($cardsIccid['origin']) || !isset($cardsIccid['virtual'])) {
                return ['status' => 'danger', 'message' => 'Невалидные параметры в ключе cards_iccid'];
            }

            /** @var Card $originCard */
            $originCard = Card::findOne(['iccid' => (int)$cardsIccid['origin']]);
            if (!$originCard) {
                throw new NotFoundHttpException("Сим-карта с ICCID#{$cardsIccid['origin']} не найдена");
            }

            /** @var Card $virtualCard */
            $virtualCard = Card::findOne(['iccid' => (int)$cardsIccid['virtual']]);
            if (!$virtualCard) {
                throw new NotFoundHttpException("Сим-карта с ICCID#{$cardsIccid['virtual']} не найдена");
            }

            // Получаем связанную модель Imsi
            $originImsies = $originCard->imsies;
            $virtualImsies = $virtualCard->imsies;

            /**
             * @var Imsi $originImsi
             * @var Imsi $virtualImsi
             */
            $originImsi = reset($originImsies);
            $virtualImsi = reset($virtualImsies);

            // Производим обмен параметров MSISDN между оригинальной и виртуальной сим-картами без временной переменной
            list($virtualImsi->msisdn, $originImsi->msisdn) = [$originImsi->msisdn, $virtualImsi->msisdn];

            // Сохраняем обновленные модели Imsi
            if (!$originImsi->save()) {
                throw new NotFoundHttpException($originImsi);
            }
            if (!$virtualImsi->save()) {
                throw new NotFoundHttpException($virtualImsi);
            }

            $transaction->commit();
            return [
                'status' => 'success',
                'message' => 'Номер успешно заменен',
                'data' => [
                    'msisdn' => [
                        'origin' => $originImsi->msisdn,
                        'virtual' => $virtualImsi->msisdn
                    ]
                ]
            ];
        } catch (NotFoundHttpException $e) {
            $transaction->rollBack();
            return ['status' => 'danger', 'message' => $e->getMessage()];
        }
    }

    /**
     * Метод создания сим-карты
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionCreateCard()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $request = $this->_getAjaxRequest();

            $card = new Card;
            $card->is_active = true;
            $card->status_id = CardStatus::ID_DEFAULT;

            /** @see loadFromInput */
            $this->_loadFromSerialize($card, $request->post());
            $transaction->commit();
            return ['status' => 'success', 'message' => 'Успешое выполнение операции по сохранение сим-карты',
                'data' => ['redirect' => $card->getUrl()]
            ];
        } catch (NotFoundHttpException $e) {
            $transaction->rollBack();
            return ['status' => 'danger', 'message' => $e->getMessage()];
        }
    }

    /**
     * Метод обновления сим-карты
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionUpdateCard()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $request = $this->_getAjaxRequest();
            $post = $request->post();

            // Переименовываем ключ виртуальной сим-карты в обычный для успешной загрузки модели
            if (isset($post['VirtualCard'])) {
                $post['Card'] = $post['VirtualCard'];
                unset($post['VirtualCard']);
            }

            if (!isset($post['Card']['iccid'])) {
                throw new NotFoundHttpException('В полученных данных отсутствует iccid параметр');
            }

            // Загружаем сим-карту, исходя из переданного параметра iccid
            $card = Card::findOne(['iccid' => (int) $post['Card']['iccid']]);
            if (!$card) { throw new NotFoundHttpException("Сим-карта с iccid#{$post['Card']['iccid']} не найдена"); }
            /** @see loadFromInput */
            $this->_loadFromSerialize($card, $post);
            $transaction->commit();
            return ['status' => 'success',
                'message' => 'Успешое выполнение операции по обновлению сим-карты',
            ];
        } catch (NotFoundHttpException $e) {
            $transaction->rollBack();
            return ['status' => 'danger', 'message' => $e->getMessage()];
        }
    }

    /**
     * Получения request и выполнение базовых настроек и проверок
     *
     * @return \yii\web\Request
     * @throws NotFoundHttpException
     */
    private function _getAjaxRequest()
    {
        $request = Yii::$app->request;
        if (!$request->isAjax) {
            throw new NotFoundHttpException('Выполняемая операция не является фоновой');
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $request;
    }

    /**
     * Общий метод обновления сим-карты и дочерних элементов
     *
     * @param Card $card
     * @param array $post
     * @throws NotFoundHttpException
     */
    private function _loadFromSerialize(Card $card, array $post)
    {
        // Сохраняем сим-карту и дочерние элементы
        if (!$card->load($post) || !$card->save()) {
            throw new NotFoundHttpException($card);
        }

        $originalChild = new Imsi;
        $originalChild->setParentId($card->primaryKey);
        $this->crudMultiple($card->imsies, $post, $originalChild);

        if ($this->validateErrors) {
            throw new NotFoundHttpException(implode('. ', $this->validateErrors));
        }
    }
}
