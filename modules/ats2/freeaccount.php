<?php


class freeAccount
{
    public function get()
    {
        $v = reservAccount::get();
        $isReserv = $v ? true : false;

        //printdbg($v, "reserv");

        if(!$isReserv)
        {
            $serial = 100000;
            $inMiss = true;

            $c = 0;
            do
            {
                if($inMiss)
                {
                    // поиск пропущенных
                    $serialMiss = self::getNextMissedAccount($serial);

                    //echo "<br>serialMiss=".$serialMiss." (".$serial.")";
                    if($serialMiss)
                        $serial = $serialMiss;
                }

                if($inMiss && !$serialMiss) 
                    $inMiss = false;

                // поиск максимального
                if(!$inMiss)
                {
                    $serialMax = self::getMaxSerial();
                    //echo "<br>serialMax=".$serialMax." (".$serialMax.")";

                    if($serialMax)
                    {
                        if($serial < $serialMax)
                            $serial = $serialMax+1;
                        else
                            $serial++;
                    }else{
                        $serial = 100001;
                    }

                }
                //echo "<br>".$serial;

                if($c++ > 10) die("c=11");
            }while(reservAccount::isReservedOther($serial));

            $v = array("serial" => $serial);
            $v["account"] = account::make($v);

            reservAccount::set($v);
        }

        return $v;
    }



    private function getNextMissedAccount($serial)
    {
        global $db_ats;

        foreach($db_ats->AllRecords(
                    "select serial 
                    from a_line
                    where 
                            is_group = 1 
                        and serial > ".$serial."
                    order by serial") as $k => $l)
        {
            //printdbg($l, "k".$k);
            if($k+1+$serial != $l["serial"]) return $k+1+$serial;
        }

        return false;
    }

    private function getMaxSerial()
    {
        global $db_ats;

        return $db_ats->GetValue("select max(serial) as v from a_line ");
    }

}
