<?php
namespace app\controllers;

use app\models\Bill;
use app\models\Client;
use app\models\HistoryChanges;
use app\models\Trouble;
use Yii;
use app\classes\BaseController;
use yii\base\Exception;
use yii\helpers\Url;

class SearchController extends BaseController
{
    public function actionIndex($search, $searchType)
    {
        $controller = 'client';
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
            case 'bills':
                if (null !== $model = Bill::find()->where(['bill_no' => trim($search)])->one()) {
                    $client = Client::findOne($model->client_id)->client;
                    return $this->redirect('/index.php?module=newaccounts&action=bill_list&clients_client=' . $client);
                } else {
                    return $this->render('result', ['message' => 'Счет № '.$search.' не найден']);
                }
            case 'troubles':
                if (null !== $model = Trouble::findOne($search)) {
                    return $this->redirect('index.php?module=tt&action=view&id=' . $model->id);
                } else {
                    return $this->render('result', ['message' => 'Заявка № '.$search.' не найдена']);
                }
            default:
                return $this->render('result', ['message' => 'Ничего не найдено']);
        }
        return $this->redirect(Url::toRoute([$controller . '/' . $action] + $params));
    }
}