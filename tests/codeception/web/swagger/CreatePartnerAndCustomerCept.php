<?php

/**
 * Создание партнера через точку подключения,
 * Создание клиента, привязанного к партнеру
 */

$I = new _WebTester($scenario);
$I->wantTo('Swagger. Add partner and customer');

$I->haveHttpHeader("Content-Type", "application/json");
$I->amBearerAuthenticated(Yii::$app->params['API_SECURE_KEY']);
$I->seeResponseIsJson();


$I->sendPOST("/api/internal/client/create/", [
    'email' => 'add_partner_' . mt_rand(0, 1000) . '@mcn.ru',
    'entry_point_id' => 'RU_PARTNER'
]);
$I->dontSee("Exception");
$I->see("status");
$I->seeResponseContainsJson(['status' => 'OK']);
$I->seeResponseContainsJson(['result' => ['is_created' => true]]);
$I->seeResponseContainsJson(['result' => ['is_partner' => true]]);

$response = $I->grabResponse();
$data = json_decode($response, true);
$partnerSuperId = $data['result']['client_id'];
$partnerAccountId = $data['result']['partner_id'];

$I->assertNotNull($partnerAccountId);
$I->assertNotEmpty($partnerAccountId);
$I->assertGreaterThan(0, $partnerAccountId);
$I->assertLessThan(100000, $partnerAccountId);

$I->assertNotNull($partnerSuperId);
$I->assertNotEmpty($partnerSuperId);

$super = \app\models\ClientSuper::findOne(['id' => $partnerSuperId]);
$I->assertNotNull($super);
$I->assertNotNull($super->contragents);
$I->assertNotNull($super->contragents[0]);
$I->assertNotNull($super->contragents[0]->contracts);
$I->assertNotNull($super->contragents[0]->contracts[0]);
$I->assertTrue($super->contragents[0]->contracts[0]->isPartner());


$I->sendPOST("/api/internal/client/create/", [
    'email' => 'add_customer_' . mt_rand(0, 1000) . '@mcn.ru',
    'partner_id' => $partnerAccountId,
    'entry_point_id' => 'RU1'
]);
$I->dontSee("Exception");
$I->see("status");
$I->seeResponseContainsJson(['status' => 'OK']);
$I->seeResponseContainsJson(['result' => ['is_created' => true]]);
$I->seeResponseContainsJson(['result' => ['is_partner_agent' => true]]);
$I->seeResponseContainsJson(['result' => ['partner_id' => $partnerAccountId]]);

$response = $I->grabResponse();
$data = json_decode($response, true);
$superId = $data['result']['client_id'];

$super = \app\models\ClientSuper::findOne(['id' => $superId]);
$I->assertNotNull($super);
$I->assertNotNull($super->contragents);
$I->assertNotNull($super->contragents[0]);
$I->assertNotNull($super->contragents[0]->contracts);
$I->assertNotNull($super->contragents[0]->contracts[0]);

$contract = $super->contragents[0]->contracts[0];

$I->assertFalse($contract->isPartner());
$I->assertNotNull($contract->isPartnerAgent());
$I->assertGreaterThan(0, $contract->isPartnerAgent());
$I->assertNotNull($contract->accounts);
$I->assertNotNull($contract->accounts[0]);

$account = $contract->accounts[0];

$accountPartner = \app\models\ClientAccount::findOne(['id' => $partnerAccountId]);

$I->assertNotNull($accountPartner);
$I->assertTrue($accountPartner->isPartner());
$I->assertNotNull($accountPartner->contract);
$I->assertNotNull($accountPartner->contract->id);
$I->assertGreaterThan(0, $accountPartner->contract->id);

$I->assertGreaterThanOrEqual(0, $account->contragent->partner_contract_id);
$I->assertEquals($account->contragent->partner_contract_id, $accountPartner->contract->id);


