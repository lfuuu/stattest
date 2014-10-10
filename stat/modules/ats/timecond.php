<?php

    /**
    * Список дней
    */
    $list_days=array(
            "-"=>"-",
            "01" => "01", "02" => "02", "03" => "03",
            "04" => "04", "05" => "05", "06" => "06",
            "07" => "07", "08" => "08", "09" => "09",
            "10" => "10", "11" => "11", "12" => "12",
            "13" => "13", "14" => "14", "15" => "15",
            "16" => "16", "17" => "17", "18" => "18",
            "19" => "19", "20" => "20", "21" => "21",
            "22" => "22", "23" => "23", "24" => "24",
            "25" => "25", "26" => "26", "27" => "27",
            "28" => "28", "29" => "29", "30" => "30",
            "31" => "31"
            );

    /**
    * Список дней в неделе
    */
    $list_weekdays = array(
            "-"   => "-" ,
            "mon" => "Понедельник",
            "tue" => "Вторник",
            "wed" => "Среда",
            "thu" => "Четверг",
            "fri" => "Пятница",
            "sat" => "Суббота",
            "sun" => "Воскресенье"
            );

    /**
    * Список месяцев
    */
    $list_months=array(
            "-"   => "-",
            "jan" => "Январь",
            "feb" => "Февраль",
            "mar" => "Март",
            "apr" => "Апрель",
            "may" => "Май",
            "jun" => "Июнь",
            "jul" => "Июль",
            "aug" => "Август",
            "sep" => "Сентябрь",
            "oct" => "Октябрь",
            "nov" => "Ноябрь",
            "dec" => "Декабрь"
            );

    /**
    * Инициализация переменных
    */

        for ($i = 0 ; $i < 24; $i++) {
            $list_hours[sprintf("%02d",$i)] = sprintf("%02d",$i);
        }

        for ($i = 0 ; $i < 60; $i++) {
            $list_mins[sprintf("%02d", $i)] = sprintf("%02d",$i);
        }

    /**
     * Редактирование интервала
     * @access interface
     */
        global $db_ado, $design;

        $do=get_param_protected("do");
        $doSet = $do == "set";

        //$id=get_param_protected('id');
        $error = false;
        $msg = "";
        $errors = array();

        if ($doSet)
        {
            if(isset($_POST['data']) && $id !== '')
            {
                $xmlStr	= $_POST['data'];
                $parse_error = false;
                $dataObj = array(
                        'time'    => array(),
                        'weekday' => array(),
                        'day'     => array(),
                        'month'   => array(),
                        'value'   => array()
                        );

                if(strlen($xmlStr))
                {
                    $xmlObj	= simplexml_load_string($xmlStr);

                    $schName = $xmlObj->name;

                    if($schName!='')
                    {
                        $schedules = $xmlObj->schedule;
                        if(sizeof($schedules))
                        {
                            $i = 0;//индексная переменная для посчета в каком по счету расписании мы находимся
                            foreach($schedules as $sch) //для каждого из расписаний
                            {
                                $dataObj['value'][$i] = '';
                                foreach($sch as $field=>$fObj) //каждое расписание разбиваем по полям
                                {
                                    
                                    switch($field)
                                    {
                                        case 'time':
                                            $val = $fObj->value;
                                            if($val!='*')
                                            {
                                                list($s,$e) = explode('-',$val);
                                                list($sh,$sm) = explode(':',$s);
                                                list($eh,$em) = explode(':',$e);

                                                $dataObj[$field][] = array('sh'=>$sh,'sm'=>$sm,'eh'=>$eh,'em'=>$em);
                                            }else{
                                                $dataObj[$field][] = array('sh'=>'99','sm'=>'99','eh'=>'99','em'=>'99');
                                            }
                                            $dataObj['value'][$i] .= $val.'|';
                                            break;//time
                                        case 'weekday':
                                        case 'day':
                                        case 'month':
                                            $val = $fObj->value;
                                            if($val!='*')
                                            {
                                                list($st,$en) = explode('-',$val,2);
                                                $dataObj[$field][] = array('start'=>$st,'end'=>$en);
                                            }else{
                                                $dataObj[$field][] = array('start'=>'*','end'=>'*');
                                            }
                                            $dataObj['value'][$i] .= $val.'|';
                                            break;//all others
                                    }//switch
                                }//end inner for
                                $dataObj['value'][$i] = substr($dataObj['value'][$i],0,strlen($dataObj['value'][$i])-1);//убираем последний знак побитового ИЛИ
                                $i++;
                            }//end outer for

                            $filler ='
                                (
                                 [!@name_id],
                                 [!@value],
                                 [!@time_sh],
                                 [!@time_sm],
                                 [!@time_eh],
                                 [!@time_em],
                                 [!@weekday_start],
                                 [!@weekday_end],
                                 [!@day_start],
                                 [!@day_end],
                                 [!@month_start],
                                 [!@month_end])
                                ';
                            $sqlArray	= array();
                            $fillers	= array();

                            foreach($dataObj as $key=>$obj)
                            {
                                foreach($obj as $ind=>$value)
                                {
                                    if(!array_key_exists($ind,$fillers))
                                    {
                                        $fillers[$ind] = $filler;
                                    }
                                    if($key!='value')
                                    {
                                        foreach($value as $ext=>$val)
                                        {
                                            $fillers[$ind] = str_replace('[!@'.$key.'_'.$ext.']',($val!='*'?'"'.$val.'"':'"-"'),$fillers[$ind]);
                                        }
                                    }else{
                                        $fillers[$ind] = str_replace('[!@'.$key.']','"'.$value.'"',$fillers[$ind]);
                                    }
                                }
                            }//FOREACH
                            /*************************_Название расписния_**************************************/
                            $sqlArray['insert_names']	=	'INSERT INTO
                                time_cond
                                VALUES
                                (NULL,"'.mysql_escape_string($fixclient).'",[!@name])
                                ';
                            /*************************_Список новых расписаний_*********************************/
                            $sqlArray['insert_schedules']	=	'INSERT INTO
                                time_cond_data
                                (name_id, value,
                                 time_start_h, time_start_m,
                                 time_end_h, time_end_m,
                                 weekday_start, weekday_end,
                                 month_day_start, month_day_end,
                                 month_start, month_end)
                                VALUES [!@fillers]';
                            /*************************_Старое название расписния_*******************************/
                            $sqlArray['update_names']	=	NULL;
                            /*************************_Список старых расписаний_********************************/
                            $sqlArray['delete_schedules']	=	NULL;

                            preg_match('/^(\d*)$/',$id,$match);//проверка на десятичное число
                            if(sizeof($match)){
                                if($id!=0){
                                    $sqlArray['delete_schedules']	= 'DELETE FROM time_cond_data WHERE name_id = "'.$id.'" ';
                                    $sqlArray['update_names']	    = 'UPDATE time_cond SET name = "'.$schName.'" WHERE id = "'.$id.'" ';
                                    $sqlArray['insert_names']	    = NULL;
                                }else{
                                    $sqlArray['insert_names'] = str_replace('[!@name]','"'.$schName.'"',$sqlArray['insert_names']);
                                }

                                $tmp = implode(',',$fillers);
                                $tmp = substr($tmp,0,strlen($tmp)-1);
                                $sqlArray['insert_schedules'] = str_replace('[!@fillers]',$tmp,$sqlArray['insert_schedules']);

                                /*Все готово, начинаем обновления*/
                                try{
                                    if(($sqlArray['update_names']!=NULL)&&($sqlArray['delete_schedules']!=NULL)){
                                        //checker::isUsed($schName, "name", "time_cond", $id, "Расписание с таким именем уже существует!");
                                        $db->Query($sqlArray['update_names']);
                                        $db->Query($sqlArray['delete_schedules']);
                                        $last_id = $id;
                                    }else{
                                        //checker::isUsed($schName, "name", "time_cond", $id, "Расписание с таким именем уже существует!");
                                        $db->Query($sqlArray['insert_names']);
                                        $last_id = $db->GetValue("SELECT LAST_INSERT_ID()");
                                    }
                                    $sqlArray['insert_schedules'] = str_replace('[!@name_id]',$last_id,$sqlArray['insert_schedules']);
                                    $db->Query($sqlArray['insert_schedules']);
                                }catch(Exception $e){
                                    $errors[] = $e->getMessage();
                                }
                            }
                        }//if sizeof schedules
                    }//if name!=''

                    if(sizeof($errors)){
                        exit(json_encode(array('error'=>$errors)));
                    }else{
                        exit(json_encode(array('result'=>'?module=ats&action=timecond')));
                    }
                }
            }

        }//IF DOSET
        /*
           выводим форму для обновления
         */

        $f = array();
        $r = array();

        if ($id == 0) {
            $title= "Добавление расписания";
        } else {
            $title= "Редактирование расписания";

            if (!$error) {
                try{
                    $sql = 'SELECT *
                        FROM  time_cond_data tc
                        INNER JOIN time_cond tcn ON (tcn.id = tc.name_id)
                        WHERE tc.name_id = "'.$id.'"
                        ORDER BY tc.id
                        ';
                    $r = $db->AllRecords($sql);
                }catch(Exception $e){

                }
            }
        }


        $design->assign("list_weekdays", $list_weekdays);
        $design->assign("list_days", $list_days);
        $design->assign("list_months", $list_months);
        $design->assign("list_hours", $list_hours);
        $design->assign("list_mins", $list_mins);

        $design->assign("msg",$msg);

        $design->assign('empty_array',array('name'=>''));
        $design->assign("data",$error ? $rGet : $r);
        $design->assign("title",$title);
        $design->assign("id",$id);

?>
