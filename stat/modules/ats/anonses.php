<?php

    /** 
    * Отдает файл уведомления
    */
function get_file($filePath)
{
    global $db, $design;

    $file = get_param_raw("file", "0");

    if($file != 0 && file_exists($filePath.$file.".mp3"))
    {
        $name = $db->GetValue("SELECT file FROM anonses WHERE id='".$file."' and ".sqlClient());

        header("Content-type: audio/mp3");
        header("Content-Disposition: attachment; filename=\"".$name.".mp3\"");
        header("Content-Length: ".filesize($filePath.$file.".mp3"));

        echo system("cat ".$filePath.$file.".mp3");
        exit();
    }else{
        die("<b style=\"color: #c40000;\">Файл не найден!</b>");
    }
}


function anonses_edit($filePath)
{
    global $db, $design;
    $do=get_param_protected("do");
    $doSet = $do == "set";
    $msg = array();


    $id=get_param_protected('id');

    if ($doSet) {
        $rGet = array("id" => $id);
        $rGet["name"] = get_param_raw("name");
        $error = false;

        if (empty($rGet["name"])) {
            $msg[] = "Введите название!";
            $error = true;
        } else {

            try{
                //checker::isUsed($rGet["name"], "name", "files", $id, "Уведомление с таким именем уже используется!");
            }catch(Exception $e){
                $error = true;
                $msg[] = "Уведомление с таким именем уже используется!";
            }
        }

        if (!$error) 
        {
            if ($id != 0) {
                $db->Query("update      anonses set name = '".$rGet["name"]."' where ".sqlClient()." and id='".$id."'");
            } else {
                $db->Query("insert into anonses set name = '".$rGet["name"]."', ".sqlClient().", id='".$id."'");
            }

            if ($id == 0) {
                $id = $db->GetValue("select last_insert_id() as id");
                $rGet["id"] = $id;
            }
        }


        if (
                $id != 0 && 
                move_uploaded_file($_FILES['upfile']['tmp_name'], $filePath.$id.".tmp")
           ) {


            // need clean
            if(saveFile($error, $msg, $id, $filePath))
            {
                $fn = $_FILES['upfile']["name"];
                $db->Query("update anonses set file ='".$db->escape(substr($fn,0,strrpos($fn, ".")))."' where id = '".$id."' and ".sqlClient());
            }

        }else{
            $error = true;
            $msg[] = "Необходимо загрузить файл";
        }


        // выводим список уведомлений если нет ошибки
        if (!$error) {
            header("Location: ./?module=ats&action=anonses");
            exit();
        }
    }

    /*
       выводим форму для обновления
     */

    $f = array();
    $r = array("name" => "", "id" => 0);

    if ($id == 0) {
        $title= "Добавление приветствия";
    } else {
        $title= "Редактирование приветствия";

        $r = $db->GetRow("SELECT * from anonses WHERE id='".$id."' and ".sqlClient());
    }



    $design->assign("uplink", 
            ( $id!=0 && file_exists($filePath.$id.".mp3") 
              ? 
              "<a href=\"./?module=ats&action=anonses&file=".$id."\">[Скачать]</a>" 
              : 
              "[Нет файла]")
            );

    $design->assign("msg",implode("<br>", $msg));
    $design->assign("data",isset($rGet) ? $rGet : $r);
    $design->assign("title",$title);
    $design->assign("id",$id);
}

function saveFile(&$error, &$msg, $id, $filePath)
{
    $fileName = $filePath.$id;
    $lamePath = "/usr/bin/lame";

    $pFile = fopen($fileName.".tmp", "rb");
    $fileHeader = fread($pFile, 15);
    fclose($pFile);

    if(substr($fileHeader, 0, 3) == "ID3") 
    {
        rename($fileName.".tmp", $fileName.".mp3");
        exec($lamePath." --decode ".$fileName.".mp3 ".$fileName.".tmp.wav", $out);
    }else
    if (
            substr($fileHeader,0,4) == "RIFF" && 
            substr($fileHeader,8,4) == "WAVE"
       )
    {
        rename($fileName.".tmp", $fileName.".tmp.wav");
    }else{
        $error[] = "Неизвестный формат. Можно MP3 или WAV.";
        $error = true;
        @unlink($fileName.".tmp");
        return false;
    }

    // resample
    exec("sox ".$fileName.".tmp.wav -r 8000 -c1 ".$fileName.".wav resample -ql", $out);
    @unlink($fileName.".tmp.wav");

    // to mp3
    exec($lamePath." -V7 ".$fileName.".wav ".$fileName.".mp3" , $out);

    // to alaw
    exec("/usr/bin/sox ".$fileName.".wav -t wav -s -r 8000 -c 1 ".$fileName.".sln resample -ql", $out);
    if (!file_exists($fileName.".sln")) {
        exec("/usr/bin/sox ".$fileName.".tmp.wav -t wav -s -r 8000 -c 1 ".$fileName.".sln", $out);
    }

    @unlink($fileName.".wav");

    exec("/usr/bin/sox ".$fileName.".sln ".$fileName.".al", $out);
    exec("mv ".$fileName.".al ".$fileName.".alaw");

    @unlink($fileName.".sln");
    return true;
}

function makeFilesFrom($format)
{
    $fileName = parent::getFileName(false);

    $lamePath = "/usr/bin/lame";


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

?>
