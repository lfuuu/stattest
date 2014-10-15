<?php
define("PATH_TO_ROOT", "../../");
define("NO_WEB", 1);

define("voip_debug", 1);
define("print_sql", 1);
define("exception_sql", 1);


include PATH_TO_ROOT."conf_yii.php";

$mDB = $db_ats;

$pDB = new PgSQLDatabase('eridanus.mcn.ru','statconv','hdfy300VGnaSdsa2', 'voipdb');
$pDB->Connect() or die("PgSQLDatabase not connected");

define("PG_SCHEMA", "astschema");

foreach($pDB->AllRecords("select * from ".PG_SCHEMA.".autolink_ip where is_synced = false") as $l)
{
    print_r($l);

    $r = false;

    if ($l["name"] && $l["srcip"])
    {
        $r = $mDB->GetRow("select * from a_line where account = '".$l["name"]."'");

        if (!$r)
            $r = $mDB->GetRow("select * from a_multitrunk where name = '".$l["name"]."'");

        if ($r)
        {
            if (($c = $mDB->GetRow("select * from a_connect where id = '".$r["c_id"]."'")) && $c["permit_on"] == "auto")
            {
                print_r($c);

                $mDB->QueryUpdate("a_connect", "id", array(
                            "id" => $c["id"],
                            "permit_on" =>"yes",
                            "permit" => implode(",", NetChecker::check($l["srcip"]))
                            )
                        );
            }
        }
    }

    $pDB->QueryUpdate(PG_SCHEMA.".autolink_ip", "id", array("id" => $l["id"], "srcip" => "", "is_synced" => true));

    if ($r)
        ats2sync::updateClient($r["client_id"]);
}


