<?php
namespace app\controllers;

use app\classes\BaseController;
use app\classes\DynamicModel;
use app\classes\validators\FormFieldValidator;
use app\exceptions\ModelValidationException;
use app\models\Bank;
use app\models\Bill;
use app\models\GoodsIncomeOrder;
use app\models\Invoice;
use app\models\Trouble;
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
                $bill = Bill::findOne(['bill_no' => trim($search)]);
                if ($bill) {
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [
                            [
                                'url' => $bill->getUrl(),
                                'value' => $bill->bill_no,
                                'type' => 'bill',
                            ]
                        ];
                    } else {
                        return $this->redirect($bill->getUrl());
                    }
                }

                $invoice = Invoice::findOne(['number' => trim($search)]);
                if ($invoice) {
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
                    } else {
                        return $this->redirect($invoice->bill->getUrl());
                    }
                }

                if (trim($search) == intval($search)) {
                    $params['id'] = intval($search);
                }

                $params['companyName'] = $search;
                break;

            case 'inn':
                $params['inn'] = intval($search);
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

            case 'bills':

                $bill = Bill::findOne(['bill_no' => trim($search)]);

                if ($bill) {
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [
                            [
                                'url' => $bill->getUrl(),
                                'value' => $bill->bill_no,
                                'type' => 'bill',
                            ]
                        ];
                    } else {
                        return $this->redirect($bill->getUrl());
                    }
                }

                return $this->render('result', ['message' => 'Счет № ' . $search . ' не найден']);

            case 'troubles':
                $trouble = Trouble::findOne($search);

                if ($trouble) {
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [['url' => $trouble->getUrl(), 'value' => $trouble->id]];
                    } else {
                        return $this->redirect($trouble->getUrl());
                    }
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
                    } else {
                        return $this->redirect($goodsIncomeOrder->getUrl());
                    }
                }

                return $this->render('result', ['message' => 'Заявка № ' . $search . ' не найдена']);

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

            default:
                return $this->render('result', ['message' => 'Ничего не найдено']);
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->request->setQueryParams($params);
            return Yii::$app->runAction($controller . '/' . $action);
        } else {
            return $this->redirect(Url::toRoute([$controller . '/' . $action] + $params + [
                'search' => $search,
                'searchType' => $searchType
            ]));
        }
    }

    /**
     * @param string $search
     * @return array
     */
    public function actionBank($search)
    {
        $models = Bank::find()->andWhere(['like', 'CAST(bik as CHAR)', $search])->all();
        $res = [];
        foreach ($models as $model) {
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
        $form = DynamicModel::validateData(Yii::$app->request->queryParams,[
            ['troubleComment', 'required'],
            ['troubleComment', FormFieldValidator::class],
            ['troubleComment', 'string', 'min' => 5]
        ]);

        if ($form->hasErrors()) {
            throw new ModelValidationException($form);
        }

        $troubleQuery = TroubleStage::find()
            ->where(['LIKE', 'comment', $form->troubleComment])
            ->limit(TroubleStage::SEARCH_ITEMS);

        if ($troubleQuery->count() == 1) {
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

        return $this->render('troubles',
            [
                'dataProvider' => $dataProvider,
            ]
        );
    }
}