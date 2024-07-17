<?php

namespace app\controllers\voip;

use app\classes\BaseController;
use app\classes\Encrypt;
use app\classes\traits\AddClientAccountFilterTraits;
use app\exceptions\web\BadRequestHttpException;
use app\models\billing\CallsCdr;
use app\models\filter\voip\MonitorFilter;
use app\models\Region;
use Yii;
use yii\web\HttpException;
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
                $searchModel->account = $this->_getCurrentClientAccountId();
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

    public function actionLoad($key, $isDownload = false)
    {
        $data = Encrypt::decodeToArray(urldecode($key));

        if (!$data || !($data['raw_server_id'] ?? false) || !($data['cdr_server_id'] ?? false) || !($data['id'] ?? false)) {
            throw new BadRequestHttpException('parameters error');
        }

        /** @var CallsCdr $cdr */
        $cdr = CallsCdr::find()->where(['server_id' => $data['cdr_server_id'], 'id' => $data['id']])->one();

        if (!$cdr) {
            throw new NotFoundHttpException('Data not found');
        }

        try {
            $file = $this->getFile($data['raw_server_id'], $cdr, $cdr->in_sig_call_id, $isDownload);
        } catch (\NotFoundHttpException $e) {
            $file = $this->getFile($data['raw_server_id'], $cdr, $cdr->out_sig_call_id, $isDownload);
        }

        if (!$file) {
            throw new NotFoundHttpException('File not found');
        }

        $fileName = preg_replace('/(\*.*$)/', '', $cdr->src_number) . '-' . $cdr->dst_number . '--' . (new \DateTime($cdr->connect_time))->modify('+3 hour')->format('Y_m_d_His') . '.wav';

        \Yii::$app->response->setDownloadHeaders($fileName, 'audio/x-wav');
        return $file;
    }

    private function getFile($rawServerId, $cdr, $sigCallId, $isFull = false)
    {
        $time = new \DateTimeImmutable($cdr->disconnect_time);

        if (in_array($cdr['server_id'],  [99, 49,85,42,86,48,47,46,58,59,45,77,44,43,57,79,56, 36,37,83,89,  50,51,52,55,38,94,53,54,31,39,  95,69,70,67,92,68,78,90,  74,93,88,64,65,96,41,66,80,63,84,62])) {
            $callHash = $cdr['hash_recordcall'];
        } else {
            $callHash = $sigCallId;
        }

        $url = 'https://' . Encrypt::decodeToArray(urldecode(\Yii::$app->params['vmonitor']['key1']))[$rawServerId]
            . '/' . $time->format('Y/m/d')
            . '/' . str_replace('-', '', $callHash) . ".wav";

//        echo $url;
//        exit();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, Encrypt::decodeString(urldecode(\Yii::$app->params['vmonitor']['key2'])));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if (!$isFull) {
            curl_setopt($curl,CURLOPT_RANGE, '0-2880000');
        }
        $return = curl_exec($curl);
        $info = curl_getinfo($curl);

        $errorNo = curl_errno($curl);
        $error = null;
        if ($errorNo) {
            $error = curl_strerror($errorNo);
        }

        curl_close($curl);

        if ($error) {
            throw new HttpException(500, $error);
        }

        if ($info['http_code'] == 404) {
            throw new NotFoundHttpException('file not found');
        }

        return $return;
    }
}
