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
        $controller = 'account';
        $action = 'index';
        $params = [];
        switch ($searchType) {
            case 'clients':
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
                if (null !== $model = Bill::find(['bill_no' => trim($search)])->one()) {
                    $client = ClientAccount::findOne($model->client_id)->client;
                    if(Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [['url' => '/index.php?module=newaccounts&action=bill_list&clients_client=' . $client, 'value' => $model->bill_no]];
                    }
                    else
                        return $this->redirect('/index.php?module=newaccounts&action=bill_list&clients_client=' . $client);
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
                        return $this->redirect('index.php?module=tt&action=view&id=' . $model->id);
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