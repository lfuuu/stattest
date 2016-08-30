<?php

namespace app\classes;

use app\models\Language as LanguageModel;
use app\classes\Language as LanguageClasses;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * Class BaseView
 * @package app\classes
 */
class BaseView extends View
{

    /**
     * добавлен только 'basePath' => '@webroot'
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
     * добавлен только 'basePath' => '@webroot'
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
     * Возвращаем путь к view-файлу формы, в зависимости от языка
     *
     * @param string $formName
     * @param string $language
     * @return string
     */
    public function getFormPath($formName, $language = LanguageModel::LANGUAGE_DEFAULT)
    {
        $formLanguage = LanguageModel::LANGUAGE_DEFAULT;

        return $this->getRealFormPath($formName, $formLanguage);
        // когда у нас появятся формы на разных языках, в разных странах, этот код понадобится
        /*
        $formLanguage = $language;

        $viewPath = $this->getRealFormPath($formName, $formLanguage);
        if ($this->isFormExists($viewPath)) {
            return $viewPath;
        }

        $formLanguage = LanguageClasses::getCurrentLanguage();
        $viewPath = $this->getRealFormPath($formName, $formLanguage);
        if ($this->isFormExists($viewPath)) {
            return $viewPath;
        }

        $formLanguage = LanguageModel::LANGUAGE_DEFAULT;

        return $this->getRealFormPath($formName, $formLanguage);
        */
    }

    /**
     * Существует ли форма
     *
     * @param $path
     * @return bool
     */
    private function isFormExists($path)
    {
        return file_exists(Yii::getAlias($path . '.php'));
    }

    /**
     * Возвращает путь к view'шке формы по имени и языку
     *
     * @param $formName
     * @param $language
     * @return string
     */
    private function getRealFormPath($formName, $language)
    {
        return '@app/views/' . $formName . '/' . $language . '/form';
    }

}