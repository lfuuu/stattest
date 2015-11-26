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
    protected function getRequestParams()
    {
        $requestData = Yii::$app->request->get();
        if (!$requestData) {
            $requestData = Yii::$app->request->bodyParams;
        }

        return $requestData;
    }

    public function runAction($id, $params = [])
    {
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
