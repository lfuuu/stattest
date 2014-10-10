<?php



class ats2mt
{

    public function getNumberMTs($numberId)
    {
        //
    }

    public function updatePoolCount($linkId)
    {
        global $db_ats;

        $trunkId = $db_ats->GetValue("select parent_id from a_multitrunk where id = '".$linkId."'");

        if($trunkId)
        {
            $count = self::countInPool($trunkId);

            $db_ats->QueryUpdate("a_multitrunk", "id", array(
                        "id" => $trunkId,
                        "call_count" => $count,
                        ));

            $db_ats->QueryUpdate("a_multitrunk", "parent_id", array(
                        "parent_id" => $trunkId,
                        "call_count" => $count,
                        ));
        }
    }


    public function countInPool($id)
    {
        global $design, $db_ats;

        if($id == 0) 
        {
            $c = 0;
        }else{
            $c = $db_ats->GetValue(
                    "select sum(n.call_count) 
                    from a_multitrunk a, a_link l, a_number n  
                    where a.parent_id = ".$id." and l.c_type='multitrunk' and l.c_id = a.id and n.id = l.number_id");                        
        }

        return $c;
    }
}

