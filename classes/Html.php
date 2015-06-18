<?php
namespace app\classes;

class Html extends \yii\helpers\Html
{
    public static function inlineImg($src, $options = [])
    {
        if (strpos($src, '://') === false) {
            $filename = \Yii::$app->basePath . '/web' . $src;
            $file = file_get_contents($filename);
            $mimeType = mime_content_type($filename);
        } else {
            $filename = tempnam('/tmp', 'img_');
            $file = file_get_contents($src);
            file_put_contents($filename, $file);
            $mimeType = mime_content_type($filename);
            unlink($filename);
        }

        $options['src'] = 'data:' . $mimeType . ';base64,' . base64_encode($file);

        return static::tag('img', '', $options);
    }
}
