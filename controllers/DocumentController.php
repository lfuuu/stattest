<?php
namespace app\controllers;

use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\ClientDocument;
use Yii;
use app\classes\BaseController;
use yii\base\Exception;
use yii\filters\AccessControl;
use app\models\Bill;
use app\classes\documents\DocumentReportFactory;
use app\classes\Assert;

class DocumentController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['clients.edit'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['print-by-code'],
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }

    public function actionActivate($id)
    {
        $model = ClientDocument::findOne($id);
        if (!$model)
            throw new Exception('Document not found');

        $isActive = !$model->is_active;
        $model->is_active = $isActive ? 1 : 0;
        $model->save();
        $this->redirect(Yii::$app->request->referrer);
    }

    public function actionCreate()
    {
        $clientDocument = new ClientDocument();
        $clientDocument->load(Yii::$app->request->post());
        $clientDocument->save();

        $this->redirect(Yii::$app->request->referrer);
    }

    public function actionEdit($id)
    {
        $model = ClientDocument::findOne($id);
        if (null === $model)
            throw new Exception('Документ не найден');

        $request = Yii::$app->request->post();

        if ($request) {
            $model->load($request);
            $model->save();
        }
        return $this->render('edit', ['model' => $model]);
    }

    public function actionSend($id)
    {
        $document = ClientDocument::findOne($id);
        if (null === $document)
            throw new Exception('Документ не найден');

        $account = $document->getAccount();
        $contact = ClientContact::find()
            ->andWhere(['client_id' => $account->id])
            ->andWhere(['is_official' => 1])
            ->andWhere(['type' => 'email'])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        $email = $contact ? $contact->data : '';

        $p = $document->getLink();
        $adr = Yii::$app->params['LK_PATH'].'docs/?code=' . str_replace('=', '%%3D', $p);
        $body = "Уважаемые Господа!" . "<br><br>" . "Отправляем Вам договор:" . "<br>";
        $body .= "<a href=\"" . $adr . "\">" . $adr . "</a><br><br>";

        echo "<html><meta http-equiv=\"refresh\" content=\"0;url=http://thiamis.mcn.ru/welltime/?module=com_agent_panel&frame=new_msg&nav=mail.none.none&message=none&subject=" . rawurlencode("MCN - договор") . "&new_msg=" . rawurlencode($body) . (!empty($email) ? "&to=" . $email : "") . "\"/><body></body></html>";
        die;
    }

    public function actionPrint($id)
    {
        $document = ClientDocument::findOne($id);
        if (null === $document)
            throw new Exception('Документ не найден');

        echo $document->getFileContent();
        die;
    }

    public function actionGetMhtml($bill_no, $doc_type = 'bill')
    {
        $bill = Bill::findOne(['bill_no' => $bill_no]);
        Assert::isObject($bill);
        $report = DocumentReportFactory::me()->getReport($bill, $doc_type, $sendEmail = 0);
        $report->renderAsMhtml();
    }

    public function actionPrintByCode($code)
    {
        $p = ClientDocument::linkDecode($code);
        $p = explode('-', $p);
        $p = array(isset($p[0]) ? intval($p[0]) : 0, isset($p[1]) ? intval($p[1]) : 0);
        $id = $p[0];
        if (!$id)
            die();

        return $this->actionPrint($id);
    }

    public function actionPrintEnvelope($clientId)
    {
        $model = ClientAccount::findOne($clientId);
        if (!$model)
            throw new Exception('ЛС не найден');
        $this->layout = false;

        return $this->render('envelope', ['account' => $model]);
    }
}