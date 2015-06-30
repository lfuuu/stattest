<?php

namespace app\helpers;

use Yii;
use app\classes\Assert;

class FileHelper extends \yii\helpers\FileHelper
{

    public static function getLocalPath()
    {
        return realpath('../') . '/web';
    }

    /**
     * @param string $mode - array [0 => ..., 1 => ...], assoc [filename1 => ..., filename2 => ...]
     */
    public static function findByPattern($path, $pattern, $mode = 'array')
    {
        Assert::isIndexExists(Yii::$app->params, $path);
        $local_part = self::getLocalPath();
        $files = glob($local_part . Yii::$app->params[$path] . $pattern, GLOB_BRACE);

        if ($mode == 'assoc') {
            $result = [];
            for ($i = 0, $s = sizeof($files); $i < $s; $i++) {
                $file_name = basename($files[$i]);
                $result[$file_name] = $file_name;
            }
            $files = $result;
        }

        return (array) $files;
    }

    public static function checkExists($path, $file_name)
    {
        Assert::isIndexExists(Yii::$app->params, $path);
        return is_file(self::getLocalPath() . Yii::$app->params[$path] . $file_name);
    }

}