<?php 

use tests\codeception\_pages\LoginPage;
use tests\codeception\_pages\ClientViewPage;

$I = new _WebTester($scenario);
$I->wantTo('perform actions and see result');

$accountId = 35801;

//$I->SetHeader("Content-type", "application/json; charset=utf-8");
$I->haveHttpHeader("Content-Type", "application/json");
$I->amBearerAuthenticated("|H;\\9P$.N4/Y\$V\\9A$#l");
$I->seeResponseIsJson();

//
//read state
//
function readState($I, $accountId)
{
    $I->sendPOST("/api/wizard-mcn/state", ["account_id" => $accountId]);
    $I->dontSee("Exception");
    $I->see("wizard_type");
}
readState($I, $accountId);
$I->seeResponseContainsJson(['wizard_type'=>'mcn']);
$I->seeResponseContainsJson(['step' => 1]);
$I->seeResponseContainsJson(['good' => 0]);

//
//read full
//
function readFull($I, $accountId)
{
    $I->sendPOST("/api/wizard-mcn/read", ["account_id" => $accountId]);
    $I->dontSee("Exception");
    $s_base = $I->grabDataFromJsonResponse();
    $s_base["account_id"] = $accountId;

    return $s_base;
}
$s_base = readFull($I, $accountId);

$I->seeResponseContainsJson([
    "step1" => [
        "name" => "test",
        "legal_type" => "legal"
    ]
]);

$I->seeResponseContainsJson([
    "step2" => [
        "link_dogovor" => "/lk/wizard/contract"
    ]
]);

$I->seeResponseContainsJson([
    "step3" => [
        "contact_phone" => "89264290001",
        "contact_fio" => "fio",
        "file_list" => [],
        "is_upload" => true
    ]
]);

$I->seeResponseContainsJson([
    "step4" => [
        "manager_name" => "",
        "manager_phone" => "(495) 105-99-99"
    ]
]);

$I->seeResponseContainsJson([
    "state" => [
        "step" => 1,
        "good" => 0,
        "wizard_type" => "mcn"
    ]
]);


//
//save step1
//
$s = [
    "step1" => [
        "name" => "OOO REP-1",
        "legal_type" => "legal",
        "address_jur" => "Краснодарский край, г Краснодар, ул Вологодская, д 11, оф 18",
        "inn" => "5020065735",
        "kpp" => "231101001",
        "position" => "Генеральный директор",
        "fio" => "Докудовский Сергей Борисович",
        "ogrn" => "",
        "last_name" => "Кашенкова",
        "first_name" => "Ксения",
        "middle_name" => "555561",
        "passport_serial" => "55",
        "passport_number" => "669",
        "passport_date_issued" => "2015-05-05",
        "passport_issued" => "6546",
        "address" => "35465"
    ],
    "step2" => [
        "link_dogovor" => "/lk/wizard/contract"
    ],
    "step3" => [
        "contact_phone" => "89264290001",
        "contact_fio" => "fio",
        "file_list" => [
        ],
        "is_upload" => true
    ],
    "step4" => [
        "manager_name" => "",
        "manager_phone" => "(495) 105-99-99"
    ],
    "state" =>[
        "step" => 1,
        "good" => 0,
        "wizard_type" => "mcn"
    ],

    "account_id" => $accountId
];


//
//save legal
//
function save_step1_legal($I, $s_base, $s)
{
    $s_step1_legal = [
        "legal_type" => "legal",
        "name" => "OOO REP-1",
        "address_jur" => "Краснодарский край, г Краснодар, ул Вологодская, д 11, оф 18",
        "inn" => "5020065735",
        "kpp" => "231101001",
        "position" => "Генеральный директор",
        "fio" => "Докудовский Сергей Борисович",
    ];



    $I->sendPOST("/api/wizard-mcn/save", merge($s_base, ["step1" => $s_step1_legal]));
    $I->dontSee("Exception");
    $I->dontSee("errors");
    $I->dontSeeResponseContainsJson([
        "step1" => [
            "name" => "test"
        ]
    ]);
    $I->seeResponseContainsJson([
        "step1" => [
            "name" => $s_step1_legal["name"]
        ]
    ]);

    $a = [
        "step2" => $s["step2"],
        "step3" => $s["step3"],
        "step4" => $s["step4"],
    ];
    $I->seeResponseContainsJson($a);

    foreach($s_step1_legal as $f => $v) {
        $I->seeResponseContainsJson(["step1" => [$f => $v]]);
    }
    $I->seeResponseContainsJson(["step1" => $s_step1_legal]);

    return $s_step1_legal;
}

save_step1_legal($I, $s_base, $s);

//
// check next step
//
$I->seeResponseContainsJson(["state" => ["good" => 1, "step" => 2]]);

readState($I, $accountId);
$I->seeResponseContainsJson(["step" => 2, "good" => 1, "wizard_type" => "mcn"]);

//
//save ip
//
function save_step1_ip($I, $s_base, $s)
{
    $s_step1_ip = [
        "name" => "ИП Иванов Иван Иванович",
        "legal_type" => "ip",
        "inn" => "344800075077",
        "ogrn" => "305346104800081",
        "last_name" => "Иванов",
        "first_name" => "Иван",
        "middle_name" => "Иванович",
        "address" => "Россия, Воронежская область, город Воронеж, Железнодорожный район, ул. Мира, д.1, кв.2"
    ];

    $I->sendPOST("/api/wizard-mcn/save", merge($s_base, ["step1" => $s_step1_ip]));
    $I->dontSee("Exception");
    $I->dontSee("errors");
    $I->dontSeeResponseContainsJson([ "step1" => [ "name" => "test" ] ]);
    $I->seeResponseContainsJson([ "step1" => [ "name" => $s_step1_ip["name"] ] ]);

    $a = [
        "step2" => $s["step2"],
        "step3" => $s["step3"],
        "step4" => $s["step4"],
    ];
    $I->seeResponseContainsJson($a);

    foreach($s_step1_ip as $f => $v) {
        $I->seeResponseContainsJson(["step1" => [$f => $v]]);
    }
    $I->seeResponseContainsJson(["step1" => $s_step1_ip]);

    return $s_step1_ip;
}

save_step1_ip($I, $s_base, $s);

//
//save1 person
//
function save_step1_person($I, $s_base, $s)
{
    $s_step1_person = [
        "legal_type" => "person",

        "last_name" => "Иванов",
        "first_name" => "Иван",
        "middle_name" => "Иванович",

        "passport_serial" => "5600",
        "passport_number" => "088855",
        "passport_date_issued" => "2015-10-05",
        "passport_issued" => "ОВД г.Воронежа",

        "address" => "Россия, Воронежская область, город Воронеж, Железнодорожный район, ул. Мира, д.1, кв.3"
    ];

    $I->sendPOST("/api/wizard-mcn/save", merge($s_base, ["step1" => $s_step1_person]));
    $I->dontSee("Exception");
    $I->dontSee("errors4");
    $I->dontSeeResponseContainsJson([ "step1" => [ "name" => "test" ] ]);
    $I->seeResponseContainsJson([
        "step1" => [ 
            "name" => 
            $s_step1_person["last_name"] . ' ' .
            $s_step1_person["first_name"] . ' ' .
            $s_step1_person["middle_name"]
        ]
    ]);

    $a = [
        "step2" => $s["step2"],
        "step3" => $s["step3"],
        "step4" => $s["step4"],
    ];
    $I->seeResponseContainsJson($a);

    foreach($s_step1_person as $f => $v) {
        $I->seeResponseContainsJson(["step1" => [$f => $v]]);
    }
    $I->seeResponseContainsJson(["step1" => $s_step1_person]);

    return $s_step1_person;
}
$s_step1_person = save_step1_person($I, $s_base, $s);

//
// save step 2
//

//contract person
function testContractHTML($I, $accountId, $s_step1)
{
    $I->sendPOST("/api/wizard-mcn/get-contract", ["account_id" => $accountId, "as_html" => 1]); //test -- для получения договора в виде html
    $I->dontSee("Exception");
    $I->dontSee("Ошибка в данных");
    $I->see("Договор оказания услуг связи");
    $I->see("№ 35801");
    foreach($s_step1 as $k => $v) 
    {
        if ($k == "legal_type" || $k == "passport_date_issued")
            continue;

        $I->see($v);
    }
}
testContractHTML($I, $accountId, $s_step1_person);

readState($I, $accountId);
$I->seeResponseContainsJson(["step" => 3, "good" => 2, "wizard_type" => "mcn"]);


//contract legal
$s_save1_legal = save_step1_legal($I, $s_base, $s);
testContractHTML($I, $accountId, $s_step1_legal);

//contract ip
$s_save1_ip = save_step1_ip($I, $s_base, $s);
testContractHTML($I, $accountId, $s_step1_ip);

//
//save step3.contact
//
$s_base = readFull($I, $accountId);

$contact_phone = "0123456789";
$contact_fio = "Тестеров Тестер";

$s_base["step3"]["contact_phone"] = $contact_phone;
$s_base["step3"]["contact_fio"] = $contact_fio;

$I->sendPOST("/api/wizard-mcn/save", $s_base);
$I->dontSee("Exception");
$I->dontSee("errors");

$I->seeResponseContainsJson( [
    "step3" => [
        "contact_phone" => $contact_phone,
        "contact_fio" => $contact_fio
    ]
]);

readState($I, $accountId);
$I->seeResponseContainsJson(["step" => 4, "good" => 3, "wizard_type" => "mcn"]);
$I->seeResponseContainsJson(["step_state" => "review"]);

//
//save step3.file
//
readFull($I, $accountId);
$I->seeResponseContainsJson([ //init state
    "step3" => ["file_list" => [], "is_upload" => true]
]);

//error saves
$I->sendPOST("/api/wizard-mcn/save-document", [
    "account_id" => $accountId
]);
$I->see("Exception");
$I->seeResponseContainsJson(["name" => "Exception", "message" => "data_error"]);

$I->sendPOST("/api/wizard-mcn/save-document", [
    "file" => [
        "name" => "file without content"
    ],
    "account_id" => $accountId
]);
$I->see("Exception");
$I->seeResponseContainsJson(["name" => "Exception", "message" => "data_error"]);

$I->sendPOST("/api/wizard-mcn/save-document", [
    "file" => [
        "content" => "file without name"
    ],
    "account_id" => $accountId
]);
$I->see("Exception");
$I->seeResponseContainsJson(["name" => "Exception", "message" => "data_error"]);


//normal save
function saveFile($I, $fileName, $accountId)
{
    $I->sendPOST("/api/wizard-mcn/save-document", [
        "file" => [
            "name" => $fileName.".file",
            "content" => base64_encode("this content for file ".$fileName.".file")
        ],
        "account_id" => $accountId
    ]);
    $I->dontSee("Exception");
    $I->dontSee("error upload file");
}

saveFile($I, "a", $accountId);
readFull($I, $accountId);
$I->seeResponseContainsJson([
    "step3" => [
        "file_list" => [
            "a.file"
        ]
    ]
]);

foreach(["b", "c", "z"] as $fileName) 
{
    saveFile($I, $fileName, $accountId);
}
readFull($I, $accountId);
$I->seeResponseContainsJson([
    "step3" => [
        "file_list" => [
            "a.file", 
            "b.file",
            "c.file",
            "z.file"
        ],
        "is_upload" => true
    ]
]);

//is_upload max
foreach(["5", "6", "7", "8", "9", "10"] as $fileName)
{
    saveFile($I, $fileName, $accountId);
}
readFull($I, $accountId);
$I->seeResponseContainsJson([
    "step3" => [
        "is_upload" => false
    ]
]);


//
//step4
//


//TODO!!! доделать проверку на шаге4. Проблема создать 2 веб-клиента.

function merge($a, $b)
{
    $args = func_get_args();
    $res = array_shift($args);
    while (!empty($args)) {
        $next = array_shift($args);
        foreach ($next as $k => $v) {
            if (is_integer($k)) {
                if (isset($res[$k])) {
                    $res[] = $v;
                } else {
                    $res[$k] = $v;
                }
            } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                $res[$k] = merge($res[$k], $v);
            } else {
                $res[$k] = $v;
            }
        }
    }

    return $res;
}
