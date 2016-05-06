<?php

namespace app\classes;

use Yii;
use yii\web\View;
use yii\helpers\ArrayHelper;
use yii\web\AssetBundle;

/**
 * Class BaseView
 * @package app\classes
 */

/**
 * @todo Удалить registerCssFile / registerJsFile / appendTimestamp
 * @todo когда в Yii добавит собственный функционал использования appendTimestamp для них
 */
class BaseView extends View
{

    /**
     * Registers a CSS file.
     * @param string $url the CSS file to be registered.
     * @param array $options the HTML attributes for the link tag. Please refer to [[Html::cssFile()]] for
     * the supported options. The following options are specially handled and are not treated as HTML attributes:
     *
     * - `depends`: array, specifies the names of the asset bundles that this CSS file depends on.
     *
     * @param string $key the key that identifies the CSS script file. If null, it will use
     * $url as the key. If two CSS files are registered with the same key, the latter
     * will overwrite the former.
     */
    public function registerCssFile($url, $options = [], $key = null)
    {
        $url = $this->appendTimestamp($url);
        $url = Yii::getAlias($url);
        $key = $key ?: $url;
        $depends = ArrayHelper::remove($options, 'depends', []);
        if (empty($depends)) {
            $this->cssFiles[$key] = Html::cssFile($url, $options);
        } else {
            $this->getAssetManager()->bundles[$key] = new AssetBundle([
                'baseUrl' => '',
                'css' => [strncmp($url, '//', 2) === 0 ? $url : ltrim($url, '/')],
                'cssOptions' => $options,
                'depends' => (array)$depends,
            ]);
            $this->registerAssetBundle($key);
        }
    }

    /**
     * Registers a JS file.
     * @param string $url the JS file to be registered.
     * @param array $options the HTML attributes for the script tag. The following options are specially handled
     * and are not treated as HTML attributes:
     *
     * - `depends`: array, specifies the names of the asset bundles that this JS file depends on.
     * - `position`: specifies where the JS script tag should be inserted in a page. The possible values are:
     *     * [[POS_HEAD]]: in the head section
     *     * [[POS_BEGIN]]: at the beginning of the body section
     *     * [[POS_END]]: at the end of the body section. This is the default value.
     *
     * Please refer to [[Html::jsFile()]] for other supported options.
     *
     * @param string $key the key that identifies the JS script file. If null, it will use
     * $url as the key. If two JS files are registered with the same key, the latter
     * will overwrite the former.
     */
    public function registerJsFile($url, $options = [], $key = null)
    {
        $url = $this->appendTimestamp($url);
        $url = Yii::getAlias($url);
        $key = $key ?: $url;
        $depends = ArrayHelper::remove($options, 'depends', []);
        if (empty($depends)) {
            $position = ArrayHelper::remove($options, 'position', self::POS_END);
            $this->jsFiles[$position][$key] = Html::jsFile($url, $options);
        } else {
            $this->getAssetManager()->bundles[$key] = new AssetBundle([
                'baseUrl' => '',
                'js' => [strncmp($url, '//', 2) === 0 ? $url : ltrim($url, '/')],
                'jsOptions' => $options,
                'depends' => (array)$depends,
            ]);
            $this->registerAssetBundle($key);
        }
    }

    /**
     * Append timestamp if the url starts with the alias 'web' and the configuration is turned on
     *
     * @param $url
     * @return string
     */
    protected function appendTimestamp($url)
    {
        if (Yii::$app->assetManager->appendTimestamp && strncmp($url, '@web/', 5) === 0) {
            $fileToCacheBust = preg_replace('#\?.*$#', '', Yii::getAlias(str_replace('@web', '@webroot', $url)));
            if (file_exists($fileToCacheBust) && ($timestamp = filemtime($fileToCacheBust)) > 0) {
                $url .= '?v=' . $timestamp;
            }
        }
        return $url;
    }

}