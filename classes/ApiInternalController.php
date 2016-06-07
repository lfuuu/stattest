<?php
namespace app\classes;

use Yii;
use yii\web\HttpException;

class ApiInternalController extends ApiController
{
    protected $requestData = null;

    public function init()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    /**
     * @return null|array
     */
    public function getRequestParams()
    {
        $this->loadRequestData();

        return $this->requestData;
    }

    /**
     * @param string $id
     * @param string $params
     * @return array
     */
    public function runAction($id, $params = [])
    {
        $this->loadRequestData();

        try {
            return [
                'status' => 'OK',
                'result' => parent::runAction($id, $params)
            ];
        } catch (\Exception $e) {
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

    /**
     * Получение входящих данных
     */
    private function loadRequestData()
    {
        $this->requestData = Yii::$app->request->get();
        if (!$this->requestData) {
            $this->requestData = Yii::$app->request->bodyParams;
        }
    }

}
