<?php

namespace app\classes\dictionary;

use app\classes\helpers\DependecyHelper;
use app\classes\Singleton;
use app\classes\Utils;
use yii\base\InvalidCallException;
use yii\caching\TagDependency;
use Yii;

class SwaggerPathMethodMap extends Singleton
{
    private $_map = [];

    public function getMethod($path)
    {
        if (!$this->_map) {
            $this->loadMap();
        }

        $path = '/' . trim($path, '/');

        return $this->_map[$path] ?? 'GET';
    }

    private function loadMap()
    {
        $key = 'swagger-method-map';
        $data = Yii::$app->cache->get($key);
        if (Yii::$app->cache->get($key) === false) {
            $data = $this->loadFromOrigin();

            Yii::$app->cache->set(
                $key,
                $data,
                DependecyHelper::TIMELIFE_HOUR,
                new TagDependency(['tags' => DependecyHelper::TAG_UU_SERVICE_LIST])
            );
        }

        $this->_map = $data;
    }

    private function loadFromOrigin()
    {
        $siteUrl = isset($_SERVER['IS_TEST']) && $_SERVER['IS_TEST'] == 1 ? 'http://stat-backend-dev-ru/' : Yii::$app->params['SITE_URL'];

        $cmd = "curl --show-error -s -X GET {$siteUrl}swagger/documentation 2>&1";

        ob_start();
        system($cmd);
        $c = ob_get_clean();

        $j = Utils::fromJson($c);

        if (!$j) {
            throw new InvalidCallException('Get methods map error');
        }

        $js = array_map(function ($a) {
            return array_values(array_keys($a))[0];
        }, $j['paths']);

        $data = [];
        foreach ($js as $path => $method) {
            $path = trim($path, '/');
            $data[$j['basePath'] . '/' . $path] = strtoupper($method);
        }

        return $data;
    }
}