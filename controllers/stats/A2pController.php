<?php

namespace app\controllers\stats;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\models\filter\A2pFilter;
use Yii;

class A2pController extends BaseController
{

    use AddClientAccountFilterTraits;

    public function actionIndex()
    {
        if (!$this->_getCurrentClientAccountId()) {
            \Yii::$app->session->addFlash('error','Не выбран ЛС');
            return $this->render('//layouts/empty', ['content' => '']);
        }
        try {
            $searchQuery = Yii::$app->request->queryParams;
            $searchModel = new A2pFilter(['isWebReport' => true]);
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
