<?php

define('NO_WEB',1);
define('PATH_TO_ROOT','../../');
include PATH_TO_ROOT."conf.php";
include INCLUDE_PATH."runChecker.php";


if(runChecker::isRun())
	die(date("r").": locked...");

runChecker::run();


    $client = "id9130";
    $password = "123123";


    echo "\n".date("r").": ";

    $perMin = 20;
    $count = 0;
    do
{
    if ($count++ > 0)
    {
        sleep(60/$perMin);
    }

    $emails = $db->AllRecords("select * from lk_notice where true order by id limit 20");


    if ($emails)
        $mailer = new Mailer();

    foreach($emails as $email)
    {
        echo "\n";
        print_r($email);
        try{
            if ($email["type"] == "email")
            {
                $mailer->send($email["data"], $email["subject"], $email["message"]);
            } elseif ($email["type"] == "phone") {


                $data = array(
                        "action" => "send",
                        "client" => $client,
                        "phone"  => $email["data"],
                        "message" => Encoding::toUtf8($email["message"]),
                        );
                
                $data["sign"] = md5($data["action"]."|".$data["client"]."|".$data["message"]."|".$data["phone"]."|".$password);

                $result = JSONQuery::exec("http://thiamis.mcn.ru/sms/gateway.php", $data, false);

                var_dump($result);

                if (isset($result["error"]))
                    throw new Exception($result["error"]);
            }


        } catch(Exception $e)
        {
            "\nerrror: ".$e->getMessage();

            $db->QueryDelete("lk_notice_settings", array("client_contact_id" => $email["contact_id"]));
            $db->QueryDelete("client_contacts", array("id" => $email["contact_id"]));
        }
        
        $db->QueryDelete("lk_notice", array("id" => $email["id"]));
    }
    if ($emails)
        unset($mailer);

    $m = (60-($count * (60/$perMin)));
    echo ".";//.date("r").": ".$m;
}while($m > 0);


runChecker::stop();

