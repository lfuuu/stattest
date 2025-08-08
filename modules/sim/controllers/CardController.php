<?php

namespace app\modules\sim\controllers;

use app\classes\BaseController;
use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\Number;
use app\models\Region;
use app\modules\nnp\models\NdcType;
use app\modules\sim\classes\workers\MsisdnsWorker;
use app\modules\sim\classes\workers\UnassignedNumberWorker;
use app\modules\sim\filters\CardFilter;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\Dsm;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\VirtualCard;
use app\modules\uu\behaviors\AccountTariffCheckHlr;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\helpers\Url;

/**
 * SIM-карты
 */
class CardController extends BaseController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'edit', 'link', 'mass-link-danycom'],
                        'roles' => ['sim.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => [
                            'new',
                            'change-msisdn',
                            'change-iccid-and-imsi',
                            'change-unassigned-number',
                            'create-card',
                            'update-card',
                            'set-esim-iccid'
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
        $account = $this->getFixClient();

        $getData = \Yii::$app->request->isPost ? Yii::$app->request->post() : Yii::$app->request->get();

        if ($account && Yii::$app->request->isGet && !isset($getData['CardFilter'])) {
            if (!isset($getData['CardFilter']['client_account_id']) || !$getData['CardFilter']['client_account_id']) {
                $getData['CardFilter']['client_account_id'] = $account->id;
            }
        }

        $filterModel = new CardFilter();
        $filterModel->load($getData);

        // for true pagination vendor/kartik-v/yii2-grid/GridView.php#741
        if (\Yii::$app->request->isPost) {
            $_GET = ['CardFilter' => $getData['CardFilter']];
        }

        $cardIccids = [];
        if (isset($getData['cardIccids']) && $getData['cardIccids'] && is_array($getData['cardIccids'])) {
            $cardIccids = $getData['cardIccids'];
        }

        $newAccountId = '';
        if (isset($getData['newAccountId']) && $getData['newAccountId']) {
            $newAccountId = $getData['newAccountId'];
        }

        if (isset($getData['cardIccids_all'])) {
            $dataProvier = $filterModel->search();
            $query = $dataProvier->query;

            $cardIccids = $query->select(Card::tableName() . '.iccid')->limit(10000)->column();

            if (count($cardIccids) >= 10000) {
                \Yii::$app->session->addFlash('error', 'Обрабатывается не более 10000 карт за раз');
            }
        }

        if ($cardIccids) {
            $isEditAllow = \Yii::$app->user->can('sim.write') || \Yii::$app->user->can('sim.link');
            if (isset($getData['set-status']) && isset($getData['status']) && $getData['status']) {
                if ($isEditAllow) {
                    Card::dao()->actionSetStatus($cardIccids, $getData['status']);
                } else {
                    \Yii::$app->session->addFlash('error', 'Действие запрещено');
                }
            } elseif (isset($getData['set-link']) && $account) {
                if ($isEditAllow) {
                    Card::dao()->actionSetLink($cardIccids, $account->id);
                } else {
                    \Yii::$app->session->addFlash('error', 'Действие запрещено');
                }

            } elseif (isset($getData['set-unlink'])) {
                if ($isEditAllow) {
                    Card::dao()->actionSetUnLink($cardIccids);
                } else {
                    \Yii::$app->session->addFlash('error', 'Действие запрещено');
                }

            } elseif (isset($getData['set-transfer'])) {
                if ($isEditAllow && $newAccountId) {
                    if (!ClientAccount::find()->where(['id' => $newAccountId])->one()) {
                        throw new InvalidParamException('Клиент не найден');
                    }
                    Card::dao()->actionSetTransfer($cardIccids, $account->id, $newAccountId);
                } else {
                    \Yii::$app->session->addFlash('error', 'Действие запрещено');
                }
            }
        }


        \Yii::$app->session->close();

        return $this->render('index', [
            'filterModel' => $filterModel,
            'account' => $account,
        ]);
    }

    /**
     * @return string
     */
    public function actionNew()
    {
        $card = new Card;
        $card->is_active = true;
        $card->status_id = CardStatus::ID_DEFAULT;

        // Создание Data State Model
        $dsm = new Dsm;
        $dsm->origin = $card;

        return $this->render('edit', ['dsm' => $dsm,]);
    }

    /**
     * Редактирование сим-карты
     *
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionEdit()
    {
        $isAllowEdit = \Yii::$app->user->can('sim.write');

        $request = Yii::$app->request;
        $queryGet = $request->getQueryParams();
        // Получение запрашиваемой сим-карты с дополнительными связями
        if (!isset($queryGet['originIccid']) || !($originCard = Card::findOne(['iccid' => $queryGet['originIccid']]))) {
            throw new NotFoundHttpException;
        }

        if ($isAllowEdit && $this->loadFromInput($originCard, $imsies, new Imsi)) {
            return $this->redirect($originCard->getUrl());
        }
        // Создание Data State Model
        $dsm = new Dsm;
        $dsm->origin = $originCard;
        $region = Region::findOne(['id' => $originCard->region_id]);
        $dsm->regionName = $region ? $region->name : '';

        // Формирование DSM по приоритету: номер - склад
        if ($isAllowEdit && $request->isPost) {
            if ($rawNumber = $request->post(Dsm::ENV_WITH_RAW_NUMBER)) {
                $dsm->rawNumber = $rawNumber;
                //  Временно пропускаем номер 79587980598, т.к. он используется на 3-х сим-картах
                if ($rawNumber === '79587980598') {
                    $dsm->errorMessages[] = sprintf('Номер %s используется на нескольких сим-картах', $rawNumber);
                    goto ret;
                }
                // Поиск запрашиваемого номера
                $number = Number::findOne([
                    'number' => $rawNumber,
                    'status' => Number::STATUS_INSTOCK, // Статус продажи
                    'ndc_type_id' => NdcType::ID_MOBILE // Только мобильные номера
                ]);
                if (!$number) {
                    $dsm->errorMessages[] = sprintf('Номер %s не найден. Возможно он не продается или не мобильный', $rawNumber);
                    goto ret;
                }
                // Поиск Imsi, для получения виртуальной сим-карты
                $virtualImsi = Imsi::findOne(['msisdn' => $number->number]);
                if ($virtualImsi && $virtualCard = VirtualCard::findOne(['iccid' => $virtualImsi->iccid])) {
                    $dsm->virtual = $virtualCard;
                } else {
                    $dsm->unassignedNumber = $number;
                }
            } else if ($warehouseStatus = $request->post(Dsm::ENV_WITH_WAREHOUSE)) {
                $dsm->warehouseId = $warehouseStatus;
                $virtual_card = VirtualCard::findOne([
                    'status_id' => $warehouseStatus,
                    'is_active' => 1, // Активный
                    'client_account_id' => null, // Не должен быть привязан
                ]);
                if (!$virtual_card) {
                    $dsm->errorMessages[] = sprintf('Виртуальная сим-карта по статусу склада %s не найдена', $warehouseStatus);
                    goto ret;
                }
                $dsm->virtual = $virtual_card;
            }
        }
        ret:
        return $this->render('edit', ['dsm' => $dsm]);
    }

    /**
     * Метод обмена MSISDN между SIM-картами
     *
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionChangeMsisdn()
    {
        $request = $this->_getAjaxRequest();
        // Выполнение синхронизации
        try {
            /** @var Imsi[] $imsies */
            $imsies = $this->_getImsiesFromRequest($request);
            // Вызов воркера, выполняющего локальные и удаленные операции
            $msisdnsWorker = new MsisdnsWorker($imsies['origin'], $imsies['virtual']);
            $response = $msisdnsWorker->callOnionSync([
                Card::getDb()->beginTransaction(),
                Number::getDb()->beginTransaction()
            ]);
            return [
                'status' => 'success',
                'data' => $response,
                'message' => sprintf(
                    'Успешная синхронизация. Подробнее: OriginImsi: %s, OriginMsisdn: %s. VirtualImsi: %s, VirtualMsisdn: %s.',
                    $imsies['origin']->imsi,
                    $response['origin_msisdn'],
                    $imsies['virtual']->imsi,
                    $response['virtual_msisdn']
                ),
            ];
        } catch (\Exception $e) {
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
        $request = $this->_getAjaxRequest();
        // Получение параметров с формы
        $originImsiParam = $request->post('origin_imsi');
        $virtualNumberParam = $request->post('virtual_number');
        // Базовые проверки данных, по которым будут загружаться OriginCard и VirtualNumber
        if (!$originImsiParam || !$virtualNumberParam) {
            return ['status' => 'danger', 'message' => 'Невалидные параметры origin_iccid или virtual_number'];
        }
        // Выполнение синхронизации
        try {
            /** @var Imsi $originImsi */
            if (!($originImsi = Imsi::findOne(['imsi' => $originImsiParam, 'partner_id' => 1]))) {
                throw new InvalidParamException(sprintf('OriginImsi %s не найдена', $originImsiParam));
            }
            // Получение свободного непривязанного мобильного номера
            $condition = [
                'number' => $virtualNumberParam,
                'status' => Number::STATUS_INSTOCK,
                'ndc_type_id' => NdcType::ID_MOBILE,
                'imsi' => null // свободный номер не должен иметь связь с сим-картой
            ];
            if (!$virtualNumber = Number::findOne($condition)) {
                throw new InvalidParamException(sprintf('Непривязанный мобильный номер с msisdn: %d не найден', $virtualNumberParam));
            }
            // Вызов воркера, выполняющего локальные и удаленные операции
            $unassignedNumberWorker = new UnassignedNumberWorker($originImsi, $virtualNumber);
            $response = $unassignedNumberWorker->callOnionSync([
                Card::getDb()->beginTransaction(),
                Number::getDb()->beginTransaction()
            ]);
            return [
                'status' => 'success',
                'data' => $response,
                'message' => sprintf(
                    'Номера успешно обменяны межды сим-картой и непривязанным номером. Детализация: OriginImsi: %s, OriginMsisdn: %s. VirtualNumber: %s.',
                    $originImsi->imsi,
                    $response['origin_msisdn'],
                    $response['virtual_number']
                ),
            ];
        } catch (\Exception $e) {
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
            return [
                'status' => 'success',
                'message' => 'Успешое выполнение операции по сохранение сим-карты',
                'data' => [
                    'redirect' => Url::to(['/sim/card/edit', 'originIccid' => $card->iccid]),
                ],
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
            return [
                'status' => 'success',
                'message' => 'Успешое выполнение операции по обновлению сим-карты',
            ];
        } catch (\Exception $e) {
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
        // Дополнительная проверка для Сим-карт, запрещая создавать более одной Imsi со статусом MVNO-партнера = 1 (MTT)
        if (isset($post['Imsi']) && $post['Imsi']) {
            $result = 0;
            foreach ($post['Imsi'] as $imsi) {
                if ($imsi['partner_id'] == 1 && ++$result > 1) {
                    throw new NotFoundHttpException('Для MVNO-партнера разрешается только один статус MTT');
                }
            }
        }

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
     * Получение сим-карт из запроса
     *
     * @param \yii\web\Request $request
     * @return array
     */
    private function _getImsiesFromRequest($request)
    {
        list($originImsiParam, $virtualImsiParam) = [
            $request->post('origin_imsi'),
            $request->post('virtual_imsi')
        ];
        // Проверка наличия требуемых параметров
        if (!$originImsiParam || !$virtualImsiParam) {
            throw new InvalidParamException('Невалидные параметры в ключе cards_iccid');
        }
        // Проверка существования моделей, которые должны иметь MVNO-партнера - MTT
        if (!($originImsi = Imsi::findOne(['imsi' => $originImsiParam, 'partner_id' => 1]))) {
            throw new InvalidParamException(sprintf('OriginImsi %s не найдена', $originImsiParam));
        }
        if (!($virtualImsi = Imsi::findOne(['imsi' => $virtualImsiParam, 'partner_id' => 1]))) {
            throw new InvalidParamException(sprintf('OriginImsi %s не найдена', $virtualImsiParam));
        }
        return ['origin' => $originImsi, 'virtual' => $virtualImsi,];
    }

    public function actionLink()
    {
        $getData = \Yii::$app->request->get();

        foreach (['link_iccid_and_number', 'account_id', 'connect_iccid', 'connect_account_tariff_id'] as $field)
            if (!isset($getData[$field])) {
                throw new \InvalidArgumentException('Не все параметры поступили');
            }

        $accountId = $getData['account_id'];
        $iccid = $getData['connect_iccid'];
        $accountTariffId = $getData['connect_account_tariff_id'];

        $info = AccountTariffCheckHlr::reservImsi([
            'account_tariff_id' => $accountTariffId,
            'card' => Card::findOne(['iccid' => $iccid]),
        ]);

        \Yii::$app->session->addFlash('success', 'ICCID ' . $info . ' присоединен');

        return \Yii::$app->response->redirect(ClientAccount::findOne(['id' => $accountId])->getUrl());
    }

    /*
    public function actionMassLinkDanycom()
    {
        $count = 0;
        $data = ClientAccount::find()
            ->alias('c')
            ->joinWith('superClient sp', true, 'INNER JOIN')
            ->where(['entry_point_id' => EntryPoint::ID_MNP_RU_DANYCOM])
            ->select('c.id');


        $return = '';
        foreach ($data->column() as $accountId) {
            [$cards, $accountTariffs] = Linker::me()->getDataByAccountId($accountId);


            $dataCards = array_keys($cards);
            $dataAccountTariffs = array_keys($accountTariffs);

            foreach ($dataAccountTariffs as $idx => $accountTariffId) {
                if (!isset($dataCards[$idx])) {
                    break;
                }

                $iccid = $dataCards[$idx];

                $return .= "<br>(" . (++$count) . ") " . $accountId . ': ' . $iccid . ' => ' . $accountTariffId . ': ' . $accountTariffs[$accountTariffId];

                if ($count > 100) {
                    break 2;
                }

                AccountTariffCheckHlr::reservImsi([
                    'account_tariff_id' => $accountTariffId,
                    'card' => Card::findOne(['iccid' => $iccid]),
                ]);
            }
        }
        return $return;
    }
    */

    /**
     * AJAX. Подключение ICCID к услуге eSIM
     *
     * @param \yii\web\Request $request
     * @return array
     */
    public function actionSetEsimIccid()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $accountTariffId = \Yii::$app->request->post('id');
        $iccid = \Yii::$app->request->post('iccid');

        $transaction1 = Card::getDb()->beginTransaction();
        $transaction2 = AccountTariff::getDb()->beginTransaction();

        try {
            $matches = [];
            if (preg_match('/^(8\d{18})\.\.\.(\d+)/', $iccid, $matches)) {
                $result = $this->doGroupSetEsimIccid($accountTariffId, $matches);
            } else {
                $result = $this->doOnceSetEsimIccid($accountTariffId, $iccid);
            }

            $transaction1->commit();
            $transaction2->commit();

        } catch (\Exception $e) {
            $transaction1->rollBack();
            $transaction2->rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        return [
                'success' => true,
            ] + $result;
    }

    private function doGroupSetEsimIccid($accountTariffId, $matches)
    {
        $maxCount = 101;
        // return ['matches' => $matches];

        $start = $matches[1];
        $end = $matches[2];

        $prefix = substr($start, 0, -strlen($end));
        $startNum = substr($start, -strlen($end));
        $endNum = $end;

        if ($startNum > $endNum) {
            throw new \InvalidArgumentException('Диапазон задан не верно');
        }

        if (($endNum - $startNum + 1) > $maxCount) {
            throw new \InvalidArgumentException("Превышено кол-во вносимых ICCID (" . ($endNum - $startNum + 1) . " > {$maxCount})");
        }

        // Создаем массив последовательности
        $iccids = [];
        for ($i = $startNum; $i <= $endNum; $i++) {
            $iccids[] = $prefix . str_pad($i, strlen($endNum), '0', STR_PAD_LEFT);
        }

        $accountTariffStart = $this->getAccountTariffById($accountTariffId);

        // проверяем - есть в наличии кол-во услуг без iccid, начиная с указанной
        $accountTariffs = AccountTariff::find()->where([
            'client_account_id' => $accountTariffStart->client_account_id,
            'service_type_id' => ServiceType::ID_ESIM,
        ])->orderBy(['id' => SORT_ASC])->all();

        $accountTariffs = array_values(array_filter($accountTariffs, fn(AccountTariff $a) => $a->iccid == ''));

        $countAccountTariffs = $accountTariffs;
        $countIccids = count($iccids);

        if ($countAccountTariffs < $countIccids) {
            throw new \InvalidArgumentException('Нет столько услуг eSim без ICCID (кол-во услуг: ' . $countAccountTariffs . ' < кол-во eSim' . $countIccids . ')');
        }

        // сортируем список услуг 

        // array_search in objects
        $startIdx = null;
        foreach ($accountTariffs as $idx => $accountTariff) {
            if ($accountTariff->id == $accountTariffStart->id) {
                $startIdx = $idx;
                break;
            }
        }

        if ($startIdx === null) {
            throw new \InvalidArgumentException('Начальная услуга не найдена (' . $accountTariffStart->id . ') ');
        }

        if ($startIdx !== 0) {
            $accountTariffs = array_merge(
                array_slice($accountTariffs, $startIdx), // часть от старта до конца
                array_slice($accountTariffs, 0, $startIdx) // часть от начала до старта
            );
        }

        // проверяем - свободны ли iccid
        array_walk($iccids, function ($iccid) use ($accountTariffStart) {
            return $this->getCardByIccid($iccid, $accountTariffStart->client_account_id, true);
        });

        // назначаем на услуги
        foreach ($accountTariffs as $accountTariff) {
            $this->doOnceSetEsimIccid($accountTariff->id, array_shift($iccids), true);
        }

        return [
            'action' => 'reloadpage',
            'client_account_id' => $accountTariffStart->client_account_id,
        ];
    }

    private function getAccountTariffById($accountTariffId)
    {
        $accountTariffId = preg_replace('/\D/', '', $accountTariffId);
        if (!$accountTariffId) {
            throw new \InvalidArgumentException('eSim не задан');
        }

        $accountTariff = AccountTariff::findOne(['id' => $accountTariffId]);
        if (!$accountTariff || $accountTariff->service_type_id != ServiceType::ID_ESIM) {
            throw new \InvalidArgumentException('Услуга не найдена');
        }

        return $accountTariff;
    }

    private function getCardByIccid($iccid, $clientAccountId, $isWithCardIccid = false): Card
    {
        $card = Card::findOne(['iccid' => $iccid]);
        if (!$card) {
            throw new \InvalidArgumentException('Карта не найдена');
        }

        $cardIccidStr = $isWithCardIccid ? ' (iccid: ' . $card->iccid . ')' : '';

        if ($card->client_account_id && $card->client_account_id != $clientAccountId) {
            throw new \InvalidArgumentException('Карта привязана к ЛС: ' . $card->client_account_id . $cardIccidStr);
        }

        $accountTariffs = AccountTariff::find()->where([
            'client_account_id' => $clientAccountId,
            'service_type_id' => ServiceType::ID_ESIM,
        ])->all();

        $isUsed = array_filter($accountTariffs, function (AccountTariff $ac) use ($iccid) {
            return $ac->iccid == $iccid;
        });
        if ($isUsed) {
            throw new \InvalidArgumentException('Карта уже привязана к этому ЛС' . $cardIccidStr);
        }

        $numbers = array_unique(array_filter(array_map(fn(Imsi $imsi) => $imsi->msisdn, $card->imsies)));
        if ($numbers) {
            throw new \InvalidArgumentException('К карте привязан номер: ' . (implode(', ', $numbers)) . $cardIccidStr);
        }

        return $card;
    }

    private function doOnceSetEsimIccid($accountTariffId, $iccid, $isWithCardIccid = false)
    {
        $iccid = preg_replace('/\D/', '', $iccid);

        if (strlen($iccid) > 20) {
            throw new \InvalidArgumentException('Формат карты не поддерживается');
        } elseif (strlen($iccid) == 20) {
            $iccid = substr($iccid, 0, 19);
        }

        $accountTariff = $this->getAccountTariffById($accountTariffId);


        // do nothing
        if (
            $accountTariff->iccid == $iccid
        ) {
            return [
                'iccid' => $iccid,
                'do' => 'nothing',
            ];
        }

        // открепляем старую карту
        if ($accountTariff->iccid) {
            $card = Card::findOne(['iccid' => $accountTariff->iccid]);
            if (!$card) {
                throw new \InvalidArgumentException('Карта в услуге не найдена');
            }

            $numbers = array_unique(array_filter(array_map(fn(Imsi $imsi) => $imsi->msisdn, $card->imsies)));
            if ($numbers) {
                throw new \InvalidArgumentException('К карте ' . $card->iccid . ' привязан номер: ' . (implode(', ', $numbers)) . '. Открепить карту нельзя');
            }

            $card->client_account_id = null;
            if (!$card->save()) {
                throw new ModelValidationException($card);
            }
        }

        $card = null;
        if ($iccid) {
            $card = $this->getCardByIccid($iccid, $accountTariff->client_account_id, $isWithCardIccid);

            $card->client_account_id = $accountTariff->client_account_id;
            if (!$card->save()) {
                throw new ModelValidationException($card);
            }
        }

        $accountTariff->iccid = $card ? $card->iccid : null;
        if (!$accountTariff->save()) {
            throw new ModelValidationException($accountTariff);
        }

        return [
            'iccid' => $iccid
        ];
    }
}
