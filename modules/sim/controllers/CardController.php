<?php

namespace app\modules\sim\controllers;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\exceptions\ModelValidationException;
use app\models\Number;
use app\modules\nnp\models\NdcType;
use app\modules\sim\classes\MttApiMvnoConnector;
use app\modules\sim\filters\CardFilter;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\CardSupport;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\VirtualCard;
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
                            'new', 'edit', 'change-msisdn', 'change-iccid-and-imsi', 'change-unassigned-number', 'create-card', 'update-card'
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
     * Метод редактирования сим-карты
     *
     * @param  integer $iccid
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionEdit($iccid)
    {
        if (!$iccid || !$originCard = Card::findOne(['iccid' => $iccid])) {
            throw new NotFoundHttpException;
        }

        $imsies = $originCard->imsies;
        if ($this->loadFromInput($originCard, $imsies, new Imsi())) {
            return $this->redirect($originCard->getUrl());
        }

        $request = Yii::$app->request;

        // Поддерживающий класс, содержащий состояние процесса
        $cardSupport = new CardSupport;
        $cardSupport->origin_card = $originCard;

        if ($statusParam = (int)$request->post('status')) {
            /*
             * Замена потерянной SIM-карты
             * Выбираем первую свободную сим-карту с заданным через параметры запроса статусом
             */
            $cardSupport->virtual_card = VirtualCard::findOne([
                'status_id' => $statusParam, 'client_account_id' => null, 'is_active' => 1,
            ]);
            $cardSupport->behaviour = CardSupport::LOST_CARD;
        } else if ($numberParam = $request->post('number')) {
            // Обмен MSISDN между сим-картами или Обмен MSISDN между сим-картой и неназначенным номером
            $number = Number::findOne([
                'number' => $numberParam, 'status' => Number::STATUS_INSTOCK, 'ndc_type_id' => NdcType::ID_MOBILE,
            ]);

            if (!$number) {
                return $this->_erroneousBehavior($cardSupport, "Свободный мобильный номер {$numberParam} не найден");
            }

            $imsi = Imsi::findOne(['msisdn' => $number->number]);
            // Нужен именно объект VirtualCard, а не Card, который можно получить через связь Imsi
            if ($imsi && $virtualCard = VirtualCard::findOne(['iccid' => $imsi->iccid])) {
                $cardSupport->virtual_card = $virtualCard;
                $cardSupport->behaviour = CardSupport::BETWEEN_CARDS;
            } else {
                if (!$number->imsi) {
                    return $this->_erroneousBehavior($cardSupport, "Номер {$numberParam} не привязан к сим-карте и не является неназначенным");
                }
                $cardSupport->unassigned_number = $number;
                $cardSupport->behaviour = CardSupport::UNASSIGNED_NUMBER;
            }
        }

        return $this->render('edit', ['cardSupport' => $cardSupport]);
    }

    /**
     * Метод обмена MSISDN между SIM-картами
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
                throw new NotFoundHttpException("OriginCard с ICCID#{$cardsIccid['origin']} не найдена");
            }

            /** @var Card $virtualCard */
            $virtualCard = Card::findOne(['iccid' => (int)$cardsIccid['virtual']]);
            if (!$virtualCard) {
                throw new NotFoundHttpException("VirtualCard с ICCID#{$cardsIccid['virtual']} не найдена");
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

            // Инициализируем историю логгирования процесса
            $initializeLogging = [
                'location' => 'initialize',
                'details' => [
                    'origin' => [
                        'card' => $originCard->getAttributes(),
                        'imsi' => $originImsi->getAttributes(),
                    ],
                    'virtual' => [
                        'card' => $virtualCard->getAttributes(),
                        'imsi' => $virtualImsi->getAttributes(),
                    ],
                ],
                'status' => 'ok',
            ];

            // Производим обмен параметров MSISDN между оригинальной и виртуальной сим-картами без временной переменной
            list($virtualImsi->msisdn, $originImsi->msisdn) = [$originImsi->msisdn, $virtualImsi->msisdn];

            // Сохраняем обновленные модели Imsi
            if (!$originImsi->save()) {
                throw new NotFoundHttpException($originImsi);
            }
            if (!$virtualImsi->save()) {
                throw new NotFoundHttpException($virtualImsi);
            }

            $logging[] = $initializeLogging;

            /**
             * Внимание. При отладке использовать два тестовых MSISDNs - 79587980447, 79587980446
             * API работает с боевым окружением. Весь процесс обмена через MVNO логгируется в Graylog
             * @link http://glogstat.mcn.ru
             *
             * Обмен MSISDN между SIM-картами. Первым параметром - Origin MSISDN, вторым - Virtual MSISDN.
             * Порядок обратный, т.к. уже произошел обмен MSISDNs между моделями в текущем контексте, но не произошел в MVNO
             */
            $this->_callChangeMsisdns($virtualImsi->msisdn, $originImsi->msisdn, $logging);

            $transaction->commit();
            return [
                'status' => 'success',
                'message' => sprintf('Произошел обмен MSISDNs с ICCID %s, имеющим номер MSISDN %d и ICCID %s, имеющим MSISDN %d',
                    $originImsi->iccid,  $virtualImsi->msisdn, $virtualImsi->iccid, $originImsi->msisdn
                ),
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
     * Метод обмена MSISDN между SIM-картой и неназначенным номером
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionChangeUnassignedNumber()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $request = $this->_getAjaxRequest();
            // Обмен MSISDN между SIM-картой и неназначенным номером
            # $this->_callChangeUnassignedNumber('', '');
            throw new NotFoundHttpException('Данный метод временно не поддерживается');
        } catch (NotFoundHttpException $e) {
            $transaction->rollBack();
            return ['status' => 'danger', 'message' => $e->getMessage()];
        }
    }

    /**
     * Метод замены потерянной SIM-карты
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionChangeIccidAndImsi()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $request = $this->_getAjaxRequest();
            // Замена потерянной SIM-карты
            # $this->_callChangeIccidAndImsi();
            throw new NotFoundHttpException('Данный метод временно не поддерживается');
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
        } catch (\Exception $e) {
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
            $card = Card::findOne(['iccid' => (int)$post['Card']['iccid']]);
            if (!$card) {
                throw new NotFoundHttpException("Сим-карта с iccid#{$post['Card']['iccid']} не найдена");
            }
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
            throw new ModelValidationException($card);
        }

        $originalChild = new Imsi;
        $originalChild->setParentId($card->primaryKey);
        $this->crudMultiple($card->imsies, $post, $originalChild);

        if ($this->validateErrors) {
            throw new \Exception(implode('. ', $this->validateErrors));
        }
    }

    /**
     * API-метод изменения MSISDNs между сим-картами
     *
     * @param string $originMsisdn
     * @param string $virtualMsisdn
     * @param array $logging
     * @throws NotFoundHttpException
     */
    private function _callChangeMsisdns($originMsisdn, $virtualMsisdn, $logging)
    {
        /** @var MttApiMvnoConnector $mttApiMvnoConnector */
        $mttApiMvnoConnector = MttApiMvnoConnector::me();
        $transferMsisdn = $mttApiMvnoConnector->getTransferMsisdn();

        // Transfer MSISDN должен быть свободен
        $currentLogging = [
            'location' => 'is_msisdn_opened',
            'details' => [
                'params' => [
                    'msisdn' => $transferMsisdn,
                ],
            ],
            'status' => 'ok',
        ];
        if (!$mttApiMvnoConnector->isMsisdnOpened($transferMsisdn)) {
            $this->_logCurrentException($logging, $currentLogging);
            throw new NotFoundHttpException('Трансферный MSISDN ' . $transferMsisdn . ' занят');
        }
        $logging[] = $currentLogging;

        // Получение и обработка originAccountMvno по Origin MSISDN
        $originAccountMvno = $mttApiMvnoConnector->getAccountData(['msisdn' => $originMsisdn]);
        $currentLogging = [
            'location' => 'get_account_data',
            'details' => [
                'params' => [
                    'msisdn' => $originMsisdn,
                ],
                'response' => $originAccountMvno->getAttributes(),
            ],
            'status' => 'ok',
        ];
        if ($originAccountMvno->isEmpty) {
            $this->_logCurrentException($logging, $currentLogging);
            throw new NotFoundHttpException('Аккаунт с MSISDN#' . $originMsisdn . ' отсутствует');
        }
        $logging[] = $currentLogging;

        // Получение и обработка virtualAccountMvno по Virtual MSISDN
        $virtualAccountMvno = $mttApiMvnoConnector->getAccountData(['msisdn' => $virtualMsisdn]);
        $currentLogging = [
            'location' => 'get_account_data',
            'details' => [
                'params' => [
                    'msisdn' => $virtualMsisdn,
                ],
                'response' => $virtualAccountMvno->getAttributes(),
            ],
            'status' => 'ok',
        ];
        if ($virtualAccountMvno->isEmpty) {
            $this->_logCurrentException($logging, $currentLogging);
            throw new NotFoundHttpException('Аккаунт с MSISDN#' . $virtualMsisdn . ' отсутствует');
        }
        $logging[] = $currentLogging;

        // В originAccountMvno освобождаем Origin MSISDN и заменяем его свободным Transfer MSISDN
        $updateCustomerOrigin = $mttApiMvnoConnector
            ->updateCustomer(['customerName' => $originAccountMvno->customer_name, 'additionalFields' => ['msisdn' => $transferMsisdn]]);
        $currentLogging = [
            'location' => 'update_customer',
            'details' => [
                'params' => [
                    'customer_name' => $originAccountMvno->customer_name,
                    'msisdn' => $transferMsisdn,
                ],
                'response' => $updateCustomerOrigin->getAttributes(),
            ],
            'status' => 'ok',
        ];

        // Проверяем, что Origin MSISDN модели originAccountMvno освобожден и заменен на Transfer MSISDN
        if (!$mttApiMvnoConnector->isMsisdnOpened($originAccountMvno->sip_id) || $mttApiMvnoConnector->isMsisdnOpened($transferMsisdn)) {
            $this->_logCurrentException($logging, $currentLogging);
            throw new NotFoundHttpException('Произошла ошибка при трансфере с MSISDN#' . $originAccountMvno->sip_id . ' на Transfer MSISDN');
        }
        $logging[] = $currentLogging;

        // В virtualAccountMvno освобождаем Virtual MSISDN и заменяем его освобожденным Origin MSISDN от originAccountMvno
        $updateCustomerVirtual = $mttApiMvnoConnector
            ->updateCustomer(['customerName' => $virtualAccountMvno->customer_name, 'additionalFields' => ['msisdn' => $originAccountMvno->sip_id]]);
        $currentLogging = [
            'location' => 'update_customer',
            'details' => [
                'params' => [
                    'customer_name' => $virtualAccountMvno->customer_name,
                    'msisdn' => $originAccountMvno->sip_id,
                ],
                'response' => $updateCustomerVirtual->getAttributes(),
            ],
            'status' => 'ok',
        ];

        // Проверяем, что Virtual MSISDN модели virtualAccountMvno освобожден и заменен освобожденным Origin MSISDN модели originAccountMvno
        if ($mttApiMvnoConnector->isMsisdnOpened($originAccountMvno->sip_id) || !$mttApiMvnoConnector->isMsisdnOpened($virtualAccountMvno->sip_id)) {
            $this->_logCurrentException($logging, $currentLogging);
            throw new NotFoundHttpException('Произошла ошибка при трансфере с MSISDN#' . $originAccountMvno->sip_id . ' на Transfer MSISDN');
        }
        $logging[] = $currentLogging;

        // В originAccountMvno освобождаем Transfer MSISDN и заменяем его освобожденным Virtual MSISDN от virtualAccountMvno
        $updateCustomerOrigin = $mttApiMvnoConnector
            ->updateCustomer(['customerName' => $originAccountMvno->customer_name, 'additionalFields' => ['msisdn' => $virtualAccountMvno->sip_id]]);
        $currentLogging = [
            'location' => 'update_customer',
            'details' => [
                'params' => [
                    'customer_name' => $originAccountMvno->customer_name,
                    'msisdn' => $virtualAccountMvno->sip_id,
                ],
                'response' => $updateCustomerOrigin->getAttributes(),
            ],
            'status' => 'ok',
        ];

        // Transfer MSISDN должен быть свободен
        if (!$mttApiMvnoConnector->isMsisdnOpened($transferMsisdn) || $mttApiMvnoConnector->isMsisdnOpened($originAccountMvno->sip_id)) {
            $this->_logCurrentException($logging, $currentLogging);
            throw new NotFoundHttpException('Трансферный MSISDN занят');
        }
        $logging[] = $currentLogging;

        Yii::warning($logging, __METHOD__);
    }

    /**
     * API-метод изменения MSISDNs между сим-картой и неназначенным номером
     *
     * @param $originMsisdn
     * @param $unassignedNumber
     * @throws NotFoundHttpException
     */
    private function _callChangeUnassignedNumber($originMsisdn, $unassignedNumber)
    {
        /** @var MttApiMvnoConnector $mttApiMvnoConnector */
        $mttApiMvnoConnector = MttApiMvnoConnector::me();
        # TODO: реализация api-метода по взаимодействия с MvnoConnector
    }

    /**
     * API-метод изменения MSISDNs между сим-картой и неназначенным номером
     *
     * @throws NotFoundHttpException
     */
    private function _callChangeIccidAndImsi()
    {
        /** @var MttApiMvnoConnector $mttApiMvnoConnector */
        $mttApiMvnoConnector = MttApiMvnoConnector::me();
        # TODO: реализация api-метода по взаимодействия с MvnoConnector
    }

    /**
     * Формирование CardSupport при ошибочном поведении
     *
     * @param CardSupport $cardSupport
     * @param string $message
     * @return string
     */
    private function _erroneousBehavior($cardSupport, $message)
    {
        $cardSupport->behaviour = CardSupport::ERROR_BEHAVIOUR;
        $cardSupport->message = $message;
        return $this->render('edit', ['cardSupport' => $cardSupport]);
    }

    /**
     * При возникновении исключительной ситуации в момент синхронизации по API с MVNO
     * изменять статус и логгировать текущее состояние
     *
     * @param array $logging
     * @param array s$currentLogging
     */
    private function _logCurrentException($logging, $currentLogging)
    {
        $currentLogging['status'] = 'exception';
        $logging[] = $currentLogging;
        Yii::warning($logging, __METHOD__);
    }
}
