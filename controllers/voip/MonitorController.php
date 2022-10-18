<?php

namespace app\controllers\voip;

use app\classes\BaseController;
use app\classes\Encrypt;
use app\classes\traits\AddClientAccountFilterTraits;
use app\exceptions\web\BadRequestHttpException;
use app\models\billing\CallsCdr;
use app\models\filter\voip\MonitorFilter;
use Yii;
use yii\web\NotFoundHttpException;

class MonitorController extends BaseController
{
    use AddClientAccountFilterTraits;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'roles' => ['voip_monitor.access'],
            ]
        ];

        return $behaviors;
    }

    public function actionIndex()
    {
        try {
            $searchQuery = Yii::$app->request->queryParams;
            $searchModel = new MonitorFilter();
            if (!$searchModel->load($searchQuery)) {
                $searchModel->orig_account = $this->_getCurrentClientAccountId();
            }
            $dataProvider = $searchModel->search(true);

            return $this->render('index', [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]);
        } catch (\Exception $e) {
            \Yii::$app->session->addFlash('error', $e->getMessage());
            return $this->render('//layouts/empty', ['content' => '']);
        }
    }

    public function actionLoad($key)
    {
        $data = Encrypt::decodeToArray(urldecode($key));

        if (!$data || !($data['server_id'] ?? false) || !($data['id'] ?? false)) {
            throw new BadRequestHttpException('parameters error');
        }

        /** @var CallsCdr $cdr */
        $cdr = CallsCdr::find()->where(['server_id' => $data['server_id'], 'id' => $data['id']])->one();

        if (!$cdr) {
            throw new NotFoundHttpException('Data not found');
        }

        try {
            return $this->getFile($cdr, $cdr->in_sig_call_id);
        } catch (\NotFoundHttpException $e) {
            return $this->getFile($cdr, $cdr->out_sig_call_id);
        }
    }

    private function getFile($cdr, $sigCallId)
    {
        $time = new \DateTimeImmutable($cdr->disconnect_time);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, Encrypt::decodeString(urldecode(\Yii::$app->params['vmonitor']['key1'])) . $time->format('Y/m/d') . "/" . str_replace('-', '', $sigCallId) . ".wav");
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, Encrypt::decodeString(urldecode(\Yii::$app->params['vmonitor']['key2'])));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($curl);
        $info = curl_getinfo($curl);

//        $errorNo = curl_errno($curl);
//        $error = curl_strerror($errorNo);
        curl_close($curl);

        if ($info['http_code'] == 404) {
            throw new NotFoundHttpException('file not found');
        }

        return $return;
    }
}
