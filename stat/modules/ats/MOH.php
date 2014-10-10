<?php


/**
* Класс для работы со звуковыми файлам music on hold
*/
class MOH_Files extends MOH
{
    private static $exts = array(".mp3", ".al",".ul",".sln",".wav", ".gsm", ".alaw", ".ulaw", ".g729");

    /**
     * Возвращает заголовок файла
     * (первые 15 символов)
     *
     * @param string $filePath путь к файлу
     * @return string/bool=false
     */
    public function getHeader()
    {
        return self::_readFile(parent::getFileName(), false);
    }

    /**
     * Возвращает файл целиком
     *
     * @return string
     */
    public function getContent()
    {
        return self::_readFile(parent::getFileName(false).".mp3", true);
    }

    /**
     * Считывание файла
     *
     * @param string $file путь к файлу
     * @param bool $isFull полностью файл считывать или только заголовок
     * @return string/bool=false
     */
    private function _readFile($file, $isFull)
    {
        if ($pFile = fopen($file, "rb")) {
            $content = "";
            while (!feof($pFile)) {
                $content .= fread($pFile, 4096);
                if(!$isFull) {
                    break;
                }
            }
            fclose($pFile);
            return $content;
        } else {
            return false;
        }
    }

    /**
     * Устанавливает новый id для звуковых файлов
     *
     * @param integer $from с какого id-а
     * @param integer $to на какой id
     * return bool
     */
    /*
    public function setNewId($from, $to)
    {
        $path = parent::getFileName($from, false);

        // проверяем на наличие нужных файлов
        foreach (self::$exts as $ext) {
            if (!is_file($path.$ext)) {
                return false;
            }
        }

        // переименовывание
        $pathNew = parent::getFileName($to, false);
        foreach (self::$exts as $ext) {
            rename($path.$ext, $pathNew.$ext);
        }
        return true;
    }
*/
    /**
     * Удаляет moh файл/файлы
     */

    public function drop($id, $fileId = false)
    {
        parent::setId($id);

        if ($fileId !== false) {
            parent::setFileId($fileId);
            return self::dropFile();
        } else {

            global $db;

            $fileList = $db->Execute(
                    "select id from music_on_hold_files " .
                    "where moh_id = '".parent::getId()."'"
                    )->GetArray();
            foreach ($fileList as $file) {
                parent::setFileId($file["id"]);
                self::dropFile();
            }
            @rmdir(parent::getDir());
        }
    }

    /**
     * Удаляем музыкальный файл
     */
    public function dropFile()
    {
        foreach (self::$exts as $ext) {
            if (!@unlink(parent::getFileName(false).$ext)) {
                //throw new Exception("Не могу удалить файл!<br>".parent::getFileName(false).$ext);
            }
        }

        global $db;
        $db->Execute(
                "delete from music_on_hold_files " .
                "where id = '".parent::$fileId."' and moh_id = '".parent::getId()."'"
                );

        return $db->Affected_Rows() > 0;
    }

    /**
     * Возвращает название файла
     *
     * @return string
     */
    public function getName()
    {
        global $db;

        $row = $db->Execute(
                "select name from music_on_hold_files " .
                "where id = '".parent::$fileId."' and moh_id = '".parent::$mohId."'"
                )->FetchRow();
        return $row["name"];
    }

}

/**
* Класс конвертации звуковых файлов
*/

class MOH_Converter extends MOH
{

    public static $isMp3 = false;


    /**
    * Ф-ия конвертации файла в форматы: gsm, wav/mp3, ulaw, alaw
    * 
    * @param integer $id ид сохраненного файла
    */
    public function convertFile($id, $fileId)
    {
        parent::$mohId = $id;
        parent::$fileId = $fileId;

        if ($content = MOH_Files::getHeader()) {
            if (($format = self::getFormat($content)) !== false) {
                if (self::makeFilesFrom($format)) {
                    return true;
                } else throw new Exception("Ошибка создания файлов!");
            } else throw new Exception("Формат файла не опрделен!");
        } else throw new Exception("Невозможно получить доступ к файлу!");
        return false;
    }

    /**
    * Создает звуковые файлы разных форматов на основе одного
    */
    private function makeFilesFrom($format)
    {
        $fileName = parent::getFileName(false);

        $lamePath = "/usr/bin/lame";
        //$lamePath = "/usr/local/bin/lame";


        if ($format == "wav") {
            rename($fileName.".tmp", $fileName.".tmp.wav");
            exec($lamePath." ".$fileName.".tmp.wav ".$fileName.".mp3");
            exec("/usr/bin/sox ".$fileName.".tmp.wav -t wav -s -r 8000 -c 1 ".$fileName.".wav resample -ql", $out);
            if (!file_exists($fileName.".wav")) {
                exec("/usr/bin/sox ".$fileName.".tmp.wav -t wav -s -r 8000 -c 1 ".$fileName.".wav", $out);
            }
        } elseif ($format == "mp3") {
            rename($fileName.".tmp", $fileName.".mp3");
            exec($lamePath." --decode ".$fileName.".mp3 ".$fileName.".tmp.wav", $out, $out2);
        } else {
            return false;
        }

        exec("/usr/bin/sox ".$fileName.".tmp.wav -t wav -s -r 8000 -c 1 ".$fileName.".sln resample -ql", $out);
        if (!file_exists($fileName.".sln")) {
            exec("/usr/bin/sox ".$fileName.".tmp.wav -t wav -s -r 8000 -c 1 ".$fileName.".sln", $out);
        }
        @unlink($fileName.".tmp.wav");

        exec("/usr/bin/sox ".$fileName.".sln ".$fileName.".al", $out);
        exec("mv ".$fileName.".al ".$fileName.".alaw");
        exec("/usr/bin/sox ".$fileName.".sln ".$fileName.".ul", $out);
        exec("mv ".$fileName.".ul ".$fileName.".ulaw");

        return true;
    }


    /**
    * Возвращает формат звукового файла
    * 
    * @param string $header заголовок файла
    * @return format
    */
    private function getFormat(&$header)
    {
        if (self::isWAV($header)) {
            return "wav";
        } elseif (self::isMP3($header)) {
            return "mp3";
        } else {
            return false;
        }
    }


    /**
    * Определяем - контент wav-файл или нет
    *
    * @param string $content содержимое файла
    * @return bool
    */
    private function isWAV(&$content)
    {
        return substr($content,0,4) == "RIFF" && 
                substr($content,8,4) == "WAVE";
    }

    /**
    * Определяем - mp3 файл или нет
    * (функция - заглушка)
    *
    * @param string $content содержимое файла
    * @return bool
    */
    private function isMP3(&$content) 
    {
        return self::$isMp3;
    }

}

?>
