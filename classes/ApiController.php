<?php
namespace app\classes;

use Yii;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\web\Controller;

/**
 * @SWG\Info(title="Внутренний СТАТ", version="2017-01-30", description="Этот документ описывает методы внутреннего API СТАТа"), consumes={"application/x-www-form-urlencoded"}, produces={"application/json"},
 * @SWG\Swagger(schemes={"https"}, host=API_HOST, basePath="/api"),
 * @SWG\Definition(definition="error_result", type="object", required={"status","result","code"},
 *   @SWG\Property(property="status", type="string", default="ERROR", description="Произошла ошибка"),
 *   @SWG\Property(property="result", type="string", description="Сообщение об ошибке"),
 *   @SWG\Property(property="code", type="integer", description="Код ошибки")
 * )
 */
class ApiController extends Controller
{
    public $serializer = 'yii\rest\Serializer';

    public $enableCsrfValidation = false;

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'authenticator' => [
                'class' => HttpBearerAuth::className(),
            ],
        ];
    }

    /**
     * @param \yii\base\Action $action
     * @param string $result
     * @return string
     */
    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        return $this->serializeData($result);
    }

    /**
     * @param mixed $data
     * @return string
     */
    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}
