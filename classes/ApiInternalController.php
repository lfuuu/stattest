<?php
namespace app\classes;

use Yii;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\HttpException;

class ApiInternalController extends ApiController
{
    protected $requestData = null;

    private function loadRequestData()
    {
        $this->requestData = Yii::$app->request->get();
        if (!$this->requestData) {
            $this->requestData = Yii::$app->request->bodyParams;
        }
    }

    public function runAction($id, $params = [])
    {
        $this->loadRequestData();

        try 
        {
            return [
                'status' => 'OK',
                'result' => parent::runAction($id, $params)
            ];
        } catch (\Exception $e)
        {
            $result = $e->getMessage();
            $code = $e->getCode();

            if ($e instanceof HttpException) {
                $code = $e->statusCode;
            }

            return [
                'status' => 'ERROR',
                'result' => $result,
                'code' => $code
            ];
        }
    }

}
