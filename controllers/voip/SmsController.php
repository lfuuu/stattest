<?php

namespace app\controllers\voip;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\models\filter\DataRawSearch;
use app\models\filter\SmsFilter;
use Yii;

class SmsController extends BaseController
{

    use AddClientAccountFilterTraits;

    public function actionIndex()
    {
        try {
            $searchQuery = Yii::$app->request->queryParams;
            $searchModel = new SmsFilter(['isWebReport' => true]);
            $searchModel->load($searchQuery);
            $dataProvider = $searchModel->search(true);

            return $this->render('index', [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]);
        } catch (\Exception $e) {
            \Yii::$app->session->addFlash('error', $e->getMessage());
            return $this->render('//layouts/empty', ['content' => '']);
        } catch (\Throwable $e) {
            $error = $e->getMessage();

            if (strpos($error, '$account_id')) {
                $error = 'Не выбран ЛС';
            }

            \Yii::$app->session->addFlash('error',$error);
            return $this->render('//layouts/empty', ['content' => '']);
        }
    }
}
