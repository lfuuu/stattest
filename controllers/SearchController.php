<?php
namespace app\controllers;

use app\models\Bill;
use app\models\ClientAccount;
use app\models\Trouble;
use Yii;
use app\classes\BaseController;
use yii\helpers\Url;
use yii\web\Response;

class SearchController extends BaseController
{
    public function actionIndex($search, $searchType)
    {
        $controller = 'client';
        $action = 'search';
        $params = [];
        switch ($searchType) {
            case 'clients':
                /////////////////////////////////////
                //Дополнительный поиск по счетам...//
                /////////////////////////////////////
                if (null !== $model = Bill::findOne(['bill_no' => trim($search)])) {
                    if(Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [['url' => '/index.php?module=newaccounts&action=bill_view&bill=' . trim($search), 'value' => $model->bill_no, 'type' => 'bill']];
                    }
                    else
                        return $this->redirect('/index.php?module=newaccounts&action=bill_view&bill=' . trim($search));
                }
                /////////////////////////////////////
                if (trim($search) == intval($search))
                    $params['id'] = intval($search);
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
                if (null !== $model = Bill::findOne(['bill_no' => trim($search)])) {
                    if(Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [['url' => '/index.php?module=newaccounts&action=bill_view&bill=' . trim($search), 'value' => $model->bill_no, 'type' => 'bill']];
                    }
                    else
                        return $this->redirect('/index.php?module=newaccounts&action=bill_view&bill=' . trim($search));
                } else {
                    return $this->render('result', ['message' => 'Счет № '.$search.' не найден']);
                }
            case 'troubles':
                if (null !== $model = Trouble::findOne($search)) {
                    if(Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [['url' => 'index.php?module=tt&action=view&id='.$model->id, 'value' => $model->id]];
                    }
                    else
                        return $this->redirect('/index.php?module=tt&action=view&id=' . $model->id);
                } else {
                    return $this->render('result', ['message' => 'Заявка № '.$search.' не найдена']);
                }
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
            default:
                return $this->render('result', ['message' => 'Ничего не найдено']);
        }
        if(Yii::$app->request->isAjax){
            Yii::$app->request->setQueryParams($params);
            return Yii::$app->runAction($controller . '/' . $action);
        }
        else
            return $this->redirect(Url::toRoute([$controller . '/' . $action] + $params + ['search' => $search, 'searchType' => $searchType]));
    }
}