<?php

namespace app\classes;

use yii\web\Response;
use Yii;

/**
 * Class ReturnFormatted
 */
class ReturnFormatted extends Singleton
{
    const FORMAT_JSON = 'json';
    const FORMAT_OPTIONS = 'options';

    /**
     * Вернуть массив в нужном формате
     *
     * @param array $values
     * @param string $format
     * @param string $defaultValue
     *
     * @throws \yii\base\ExitException
     */
    public function returnFormattedValues($values, $format, $defaultValue = '')
    {
        $response = Yii::$app->getResponse();

        switch ($format) {
            case self::FORMAT_OPTIONS:
                $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
                $response->format = Response::FORMAT_HTML;
                echo Html::renderSelectOptions($defaultValue, $values);
                break;

            case self::FORMAT_JSON:
            default:
                $response->headers->set('Content-Type', 'application/json');
                $response->format = Response::FORMAT_JSON;
                echo json_encode($values);
                break;
        }

        Yii::$app->end();
    }
}
