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

}
