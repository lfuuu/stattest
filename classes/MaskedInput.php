<?php


namespace app\classes;


use yii\helpers\Json;
use yii\widgets\MaskedInputAsset;

/**
 * Class MaskedInput
 *
 * TODO: remove after update yii2 ver>=2.0.8
 *
 * @package app\classes
 */
class MaskedInput extends \yii\widgets\MaskedInput
{
    /**
     * Registers the needed client script and options.
     */
    public function registerClientScript()
    {
        $js = '';
        $view = $this->getView();
        $this->initClientOptions();
        if (!empty($this->mask)) {
            $this->clientOptions['mask'] = $this->mask;
        }
        $this->hashPluginOptions($view);
        if (is_array($this->definitions) && !empty($this->definitions)) {
            $js .= ucfirst(self::PLUGIN_NAME) . '.extendDefinitions(' . Json::htmlEncode($this->definitions) . ');';
        }
        if (is_array($this->aliases) && !empty($this->aliases)) {
            $js .= ucfirst(self::PLUGIN_NAME) . '.extendAliases(' . Json::htmlEncode($this->aliases) . ');';
        }
        $id = $this->options['id'];
        $js .= '$("#' . $id . '").' . self::PLUGIN_NAME . '(' . $this->_hashVar . ');';
        MaskedInputAsset::register($view);
        $view->registerJs($js);
    }

}