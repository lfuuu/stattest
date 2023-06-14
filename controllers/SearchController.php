<?php

namespace app\controllers;

use app\classes\BaseController;
use app\classes\DynamicModel;
use app\classes\validators\FormFieldValidator;
use app\models\Bank;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\ClientContragent;
use app\models\GoodsIncomeOrder;
use app\models\Invoice;
use app\models\Trouble;
use app\models\TroubleRoistat;
use app\models\TroubleStage;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\Response;

class SearchController extends BaseController
{
    /**
     * @param string $search
     * @param string $searchType
     * @return int|mixed|string|\yii\console\Response|Response
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex($search, $searchType)
    {
        $search = trim($search);
        $controller = 'client';
        $action = 'search';
        $params = [];
        switch ($searchType) {

            case 'clients':
                if (isset(Yii::$app->request->queryParams['term'])) {
                    $search = Yii::$app->request->queryParams['term'];
                    $params['is_term'] = true;
                }

                // Дополнительный поиск по счетам
                if ($bill = Bill::findOne(['bill_no' => trim($search)])) {
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [
                            [
                                'url' => $bill->getUrl(),
                                'value' => $bill->bill_no,
                                'type' => 'bill',
                            ]
                        ];
                    }
                    return $this->redirect($bill->getUrl());
                }

                if ($invoice = Invoice::findOne(['number' => trim($search)])) {
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [
                            [
                                'url' => $invoice->bill->getUrl(),
                                'value' => $invoice->number,
                                'type_id' => $invoice->type_id,
                                'is_reversal' => $invoice->is_reversal,
                                'bill_no' => $invoice->bill->bill_no,
                                'type' => 'invoice',
                            ]
                        ];
                    }
                    return $this->redirect($invoice->bill->getUrl());
                }

                if (is_numeric($search)) {
                    $params['id'] = (int)$search;
                }

                $params['companyName'] = $search;
                break;

            case 'inn':
                $params['inn'] = (int)$search;
                break;

            case 'voip':
                $params['voip'] = trim($search);
                break;

            case 'email':
                $params['email'] = trim($search);
                break;

            case 'contractNo':
                $params['contractNo'] = trim($search);
                break;

            case 'contragentNo':
                $contragent = ClientContragent::findOne(['id' => trim($search)]);
                if ($contragent) {
                    $account = null;
                    $contract = reset($contragent->contracts);
                    if ($contract) {
                        $accounts = $contract->accounts;
                        if ($accounts) {
                            $accountsFiltred = array_filter($accounts, function (ClientAccount $account) {
                                return $account->is_active;
                            });

                            if ($accountsFiltred) {
                                $accounts = $accountsFiltred;
                            }
                            $account = reset($accounts);
                        }
                    }

                    return $this->redirect([
                            '/contragent/edit', 'id' => $contragent->id
                        ] + ($account ? ['childId' => $account->id] : []));
                }

                return $this->render('result', ['message' => 'Контрагент № ' . $search . ' не найден']);

            case 'bills':

                if ($bill = Bill::findOne(['bill_no' => trim($search)])) {
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [
                            [
                                'url' => $bill->getUrl(),
                                'value' => $bill->bill_no,
                                'type' => 'bill',
                            ]
                        ];
                    }

                    return $this->redirect($bill->getUrl());
                }

                return $this->render('result', ['message' => 'Счет № ' . $search . ' не найден']);

            case 'troubles':

                if ($trouble = Trouble::findOne($search)) {
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [['url' => $trouble->getUrl(), 'value' => $trouble->id]];
                    }
                    return $this->redirect($trouble->getUrl());
                }

                $goodsIncomeOrder = GoodsIncomeOrder::find()
                    ->where(['number' => $search])
                    ->orderBy(['date' => SORT_DESC])
                    ->limit(1)
                    ->one();

                if ($goodsIncomeOrder) {
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [
                            [
                                'url' => $goodsIncomeOrder->getUrl(),
                                'value' => $goodsIncomeOrder->id,
                            ]
                        ];
                    }
                    return $this->redirect($goodsIncomeOrder->getUrl());
                }

                return $this->render('result', ['message' => 'Заявка № ' . $search . ' не найдена']);

            case 'roistat_visit':

                $countRoistatVisit = TroubleRoistat::find()
                    ->select('trouble_id')
                    ->where(['roistat_visit' => $search])
                    ->count();

                if ($countRoistatVisit == 1) {
                    $troubleRoistat = TroubleRoistat::find()
                        ->select('trouble_id')
                        ->where(['roistat_visit' => $search])
                        ->scalar();

                    if ($trouble = Trouble::findOne($troubleRoistat)) {
                        if (Yii::$app->request->isAjax) {
                            Yii::$app->response->format = Response::FORMAT_JSON;
                            return [['url' => $trouble->getUrl(), 'value' => $trouble->id]];
                        }
                        return $this->redirect($trouble->getUrl());
                    }
                    return $this->render('result', ['message' => 'Заявка c roistat visit ' . $search . ' не найдена!']);
                } else {
                    $searchRoistatVisit = Trouble::find()
                        ->join('INNER JOIN', 'tt_troubles_roistat', 'tt_troubles.id = tt_troubles_roistat.trouble_id')
                        ->where(['tt_troubles_roistat.roistat_visit' => $search])
                        ->orderBy(['date_creation' => SORT_DESC]);

                    $dataProvider = new ActiveDataProvider([
                        'query' => $searchRoistatVisit
                    ]);
                    return $this->render('roistat', ['dataProvider' => $dataProvider,]);
                }
                break;

            case 'troubleText':
                /** @var Trouble $model */
                $trouble = Trouble::find()
                    ->where(['LIKE', 'problem', $search])
                    ->orderBy(['id' => SORT_DESC])
                    ->one();

                if ($trouble) {
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [['url' => $trouble->getUrl(), 'value' => $trouble->id]];
                    }
                    return $this->redirect($trouble->getUrl());
                }

                return $this->render('result', ['message' => 'Ничего не найдено']);
                break;

            case 'troubleComment':
                $controller = 'search';
                $action = 'trouble';
                $params['troubleComment'] = trim($search);
                break;

            case 'ip':
                $params['ip'] = trim($search);
                break;

            case 'domain':
                $params['domain'] = trim($search);
                break;

            case 'address':
                $params['address'] = trim($search);
                break;

            case 'adsl':
                $params['adsl'] = trim($search);
                break;

            case 'contactPhone':
                $params['contactPhone'] = trim($search);
                break;

            case 'sip':
                $params['sip'] = trim($search);
                break;

            default:
                return $this->render('result', ['message' => 'Ничего не найдено']);
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->request->setQueryParams($params);
            return Yii::$app->runAction($controller . '/' . $action);
        }
        return $this->redirect(Url::toRoute(
            [$controller . '/' . $action] +
            $params +
            ['search' => $search, 'searchType' => $searchType]
        ));
    }

    /**
     * @param string $search
     * @return array
     */
    public function actionBank($search)
    {
        $res = [];
        $models = Bank::find()
            ->andWhere(['like', 'CAST(bik as CHAR)', $search]);
        foreach ($models->each() as $model) {
            $res[] = [
                'value' => $model->bik,
                'bank_name' => $model->bank_name,
                'bank_city' => $model->bank_city,
                'corr_acc' => $model->corr_acc,
            ];
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $res;
    }

    public function actionTrouble()
    {
        $form = DynamicModel::validateData(Yii::$app->request->queryParams, [
            ['troubleComment', 'required'],
            ['troubleComment', FormFieldValidator::class],
            ['troubleComment', 'string', 'min' => 4]
        ]);

        $form->validateWithException();

        $troubleQuery = TroubleStage::find()
            ->where(['LIKE', 'comment', $form->troubleComment])
            ->limit(TroubleStage::SEARCH_ITEMS);

        if ((int)$troubleQuery->count() === 1) {
            return $this->redirect($troubleQuery->one()->trouble->getUrl());
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $troubleQuery,
            'sort' => [
                'defaultOrder' => [
                    'stage_id' => SORT_DESC
                ]
            ]
        ]);

        return $this->render('troubles', ['dataProvider' => $dataProvider,]);
    }
}