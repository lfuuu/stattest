<?php

namespace app\classes;

use app\assets\AppAsset;
use app\models\Language as LanguageModel;
use app\classes\Language as LanguageClasses;
use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * Class BaseView
 * @package app\classes
 */
class BaseView extends View
{

    const ASSET_FRONTEND_DIR = '/views';

    private $_viewFile;
    private $_jsVariables = [];

    /**
     * @return string
     * @throws InvalidParamException
     */
    protected function renderHeadHtml()
    {
        // Регистрация frontend переменных
        if (count($this->_jsVariables)) {
            $this->registerJs('var frontendVariables = ' . Json::encode($this->_jsVariables), self::POS_HEAD);
        }

        return parent::renderHeadHtml();
    }

    /**
     * @param string $view
     * @param array $params
     * @param string $context
     * @return string
     * @throws InvalidParamException
     * @throws InvalidCallException
     * @throws InvalidConfigException
     */
    public function render($view, $params = [], $context = null)
    {
        $this->_viewFile = $viewFile = $this->findViewFile($view, $context);
        $this->_viewFile = realpath($this->_viewFile);

        // Убрать путь до каталога с views
        $this->_viewFile = str_replace([Yii::$app->getBasePath(), '/views'], '', $this->_viewFile);

        // Убрать последний разделитель каталогов
        $this->_viewFile = ltrim($this->_viewFile, DIRECTORY_SEPARATOR);

        // Убрать расширение PHP скриптов
        $this->_viewFile = str_replace('.' . $this->defaultExtension, '', $this->_viewFile);

        // Регистрация frontend файлов
        $this->_loadFrontend();

        return $this->renderFile($viewFile, $params, $context);
    }

    /**
     * Добавлен только 'basePath' => '@webroot'
     *
     * @param string $url
     * @param array $options
     * @param null $key
     * @throws InvalidConfigException
     * @throws InvalidParamException
     */
    public function registerCssFile($url, $options = [], $key = null)
    {
        $url = Yii::getAlias($url);
        $key = $key ?: $url;
        $depends = ArrayHelper::remove($options, 'depends', []);

        if (empty($depends)) {
            $this->cssFiles[$key] = Html::cssFile($url, $options);
        } else {
            $this->getAssetManager()->bundles[$key] = new AssetBundle([
                'baseUrl' => '',
                'basePath' => '@webroot',
                'css' => [strncmp($url, '//', 2) === 0 ? $url : ltrim($url, '/')],
                'cssOptions' => $options,
                'depends' => (array)$depends,
            ]);
            $this->registerAssetBundle($key);
        }
    }

    /**
     * Добавлен только 'basePath' => '@webroot'
     *
     * @param string $url
     * @param array $options
     * @param null $key
     * @throws InvalidConfigException
     * @throws InvalidParamException
     */
    public function registerJsFile($url, $options = [], $key = null)
    {
        $url = Yii::getAlias($url);
        $key = $key ?: $url;
        $depends = ArrayHelper::remove($options, 'depends', []);

        if (empty($depends)) {
            $position = ArrayHelper::remove($options, 'position', self::POS_END);
            $this->jsFiles[$position][$key] = Html::jsFile($url, $options);
        } else {
            $this->getAssetManager()->bundles[$key] = new AssetBundle([
                'baseUrl' => '',
                'basePath' => '@webroot',
                'js' => [strncmp($url, '//', 2) === 0 ? $url : ltrim($url, '/')],
                'jsOptions' => $options,
                'depends' => (array)$depends,
            ]);
            $this->registerAssetBundle($key);
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param string $viewJsKey
     * @throws InvalidParamException
     */
    public function registerJsVariable($name, $value, $viewJsKey = '')
    {
        $sectionKey = empty($viewJsKey) ? $this->_getViewJsKey() : $viewJsKey;
        $this->_jsVariables[$sectionKey][$name] = $value;
    }

    /**
     * @param array $variables
     * @param string $viewJsKey
     */
    public function registerJsVariables(array $variables, $viewJsKey = '')
    {
        $sectionKey = empty($viewJsKey) ? $this->_getViewJsKey() : $viewJsKey;
        if (!isset($this->_jsVariables[$sectionKey])) {
            $this->_jsVariables[$sectionKey] = [];
        }

        $this->_jsVariables[$sectionKey] = array_merge((array)$this->_jsVariables[$sectionKey], $variables);
    }

    /**
     * Возвращаем путь к view-файлу формы, в зависимости от языка
     *
     * @param string $formName
     * @param string $language
     * @return string
     */
    public function getFormPath($formName, $language = LanguageModel::LANGUAGE_DEFAULT)
    {
        $formLanguage = LanguageModel::LANGUAGE_DEFAULT;

        return $this->_getRealFormPath($formName, $formLanguage);

        /*
            // когда у нас появятся формы на разных языках, в разных странах, этот код понадобится
            $formLanguage = $language;

            $viewPath = $this->_getRealFormPath($formName, $formLanguage);
            if ($this->_isFormExists($viewPath)) {
                return $viewPath;
            }

            $formLanguage = LanguageClasses::getCurrentLanguage();
            $viewPath = $this->_getRealFormPath($formName, $formLanguage);
            if ($this->_isFormExists($viewPath)) {
                return $viewPath;
            }

            $formLanguage = LanguageModel::LANGUAGE_DEFAULT;

            return $this->_getRealFormPath($formName, $formLanguage);
        */

    }

    /**
     * Существует ли форма
     *
     * @param string $path
     * @return bool
     */
    private function _isFormExists($path)
    {
        return file_exists(Yii::getAlias($path . '.php'));
    }

    /**
     * Возвращает путь к view'шке формы по имени и языку
     *
     * @param string $formName
     * @param string $language
     * @return string
     */
    private function _getRealFormPath($formName, $language)
    {
        return '@app/views/' . $formName . '/' . $language . '/form';
    }

    /**
     * @return string
     */
    private function _getViewJsKey()
    {
        return Inflector::variablize($this->_viewFile);
    }

    /**
     * @throws InvalidConfigException
     * @throws InvalidParamException
     */
    private function _loadFrontend()
    {
        if (!empty($this->_viewFile)) {
            $assetViewFile = self::ASSET_FRONTEND_DIR . DIRECTORY_SEPARATOR . $this->_viewFile;

            do {
                if (file_exists(Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . ($assetJsView = $assetViewFile . '.js'))) {
                    $this->registerJsFile($assetJsView, ['depends' => [AppAsset::className(),]]);
                }

                if (file_exists(Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . ($assetCssView = $assetViewFile . '.css'))) {
                    $this->registerCssFile($assetCssView, ['depends' => [AppAsset::className(),]]);
                }

                $parts = explode(DIRECTORY_SEPARATOR, trim($assetViewFile, DIRECTORY_SEPARATOR));
                $parts = array_slice($parts, 0, count($parts) - 1);
                $assetViewFile = implode(DIRECTORY_SEPARATOR, $parts);
            } while (count($parts));
        }
    }

}