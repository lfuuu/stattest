<?php
namespace app\classes;

use app\models\User;
use Yii;
use yii\web\HttpException;

class ApiInternalController extends ApiController
{
    protected $requestData = null;

    /**
     * Init
     */
    public function init()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    /**
     * @return null|array
     */
    public function getRequestParams()
    {
        $this->_loadRequestData();

        return $this->requestData;
    }

    /**
     * @param string $id
     * @param string[] $params
     * @return array
     */
    public function runAction($id, $params = [])
    {
        $this->_loadRequestData();

        try {
            return [
                'status' => 'OK',
                'result' => parent::runAction($id, $params)
            ];
        } catch (\Exception $e) {
            $result = $e->getMessage();
            $code = $e->getCode();

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
    private function _loadRequestData()
    {
        $this->requestData = Yii::$app->request->get();
        if (!$this->requestData) {
            $this->requestData = Yii::$app->request->bodyParams;
        }

        if ($this->requestData && isset($this->requestData['is_from_lk']) && $this->requestData['is_from_lk']) {
            \Yii::$app->user->setIdentity(User::findOne(['id' => User::LK_USER_ID]));
        }
    }
}
