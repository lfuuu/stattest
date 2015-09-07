<?php
namespace app\classes;

class Html extends \yii\helpers\Html
{

    public static function inlineImg($src, $options = [], $mimeType = false)
    {
        if (strpos($src, '://') === false) {
            $filename = \Yii::$app->basePath . '/web' . $src;
            $file = file_get_contents($filename);
            if ($mimeType === false)
                $mimeType = mime_content_type($filename);
        } else {
            $filename = tempnam('/tmp', 'img_');
            $file = file_get_contents($src);
            if ($mimeType === false)
                $mimeType = mime_content_type($filename);
            file_put_contents($filename, $file);
            unlink($filename);
        }

        $options['src'] = 'data:' . $mimeType . ';base64,' . base64_encode($file);

        return static::tag('img', '', $options);
    }

    public static function formLabel($text, $options = [])
    {
        return parent::tag('legend', $text, $options);
    }

    public static function renderFormBtns($backUrl = '')
    {
        return
            parent::tag(
                'div',
                parent::button('Отменить', [
                    'class' => 'btn btn-link',
                    'style' => 'margin-right: 15px;',
                    'onClick' => 'self.location = "' . ($backUrl ?: '/') . '";',
                ]) .
                parent::submitButton('Сохранить', ['class' => 'btn btn-primary', 'id' => 'buttonSave']),
                ['style' => 'text-align: right; padding-right: 0px;']
            );
    }

}
