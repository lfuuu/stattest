ALTER TABLE `client_contract`
	ADD COLUMN `organization_id` INT NOT NULL DEFAULT '0' AFTER `organization`;

UPDATE client_contract cc
	INNER JOIN clients c ON c.contract_id = cc.id
	SET cc.`organization_id` = c.`organization_id`;

ALTER TABLE `client_contract`
	DROP COLUMN `organization`;

UPDATE history_version SET data_json = REPLACE(data_json, '"organization":"mcn_telekom"', '"organization_id":1');
UPDATE history_version SET data_json = REPLACE(data_json, '"organization":"ooomcn"', '"organization_id":2');
UPDATE history_version SET data_json = REPLACE(data_json, '"organization":"mcn"', '"organization_id":3');
UPDATE history_version SET data_json = REPLACE(data_json, '"organization":"mcm"', '"organization_id":4');
UPDATE history_version SET data_json = REPLACE(data_json, '"organization":"ooocmc"', '"organization_id":5');
UPDATE history_version SET data_json = REPLACE(data_json, '"organization":"all4geo"', '"organization_id":6');
UPDATE history_version SET data_json = REPLACE(data_json, '"organization":"all4net"', '"organization_id":7');
UPDATE history_version SET data_json = REPLACE(data_json, '"organization":"wellstart"', '"organization_id":8');
UPDATE history_version SET data_json = REPLACE(data_json, '"organization":"mcn_telekom_hungary"', '"organization_id":9');
UPDATE history_version SET data_json = REPLACE(data_json, '"organization":"tel2tel_hungary"', '"organization_id":10');
UPDATE history_version SET data_json = REPLACE(data_json, '"organization":"mcm_telekom"', '"organization_id":11');
UPDATE history_version SET data_json = REPLACE(data_json, '"organization":"markomnet_service"', '"organization_id":13');
UPDATE history_version SET data_json = REPLACE(data_json, '"organization":"markomnet"', '"organization_id":14');
UPDATE history_version SET data_json = REPLACE(data_json, '"organization":"markomnet_new"', '"organization_id":15');
UPDATE history_version SET data_json = REPLACE(data_json, '"organization":""', '"organization_id":1');

UPDATE history_changes SET data_json = REPLACE(data_json, '"organization":"mcn_telekom"', '"organization_id":1');
UPDATE history_changes SET data_json = REPLACE(data_json, '"organization":"ooomcn"', '"organization_id":2');
UPDATE history_changes SET data_json = REPLACE(data_json, '"organization":"mcn"', '"organization_id":3');
UPDATE history_changes SET data_json = REPLACE(data_json, '"organization":"mcm"', '"organization_id":4');
UPDATE history_changes SET data_json = REPLACE(data_json, '"organization":"ooocmc"', '"organization_id":5');
UPDATE history_changes SET data_json = REPLACE(data_json, '"organization":"all4geo"', '"organization_id":6');
UPDATE history_changes SET data_json = REPLACE(data_json, '"organization":"all4net"', '"organization_id":7');
UPDATE history_changes SET data_json = REPLACE(data_json, '"organization":"wellstart"', '"organization_id":8');
UPDATE history_changes SET data_json = REPLACE(data_json, '"organization":"mcn_telekom_hungary"', '"organization_id":9');
UPDATE history_changes SET data_json = REPLACE(data_json, '"organization":"tel2tel_hungary"', '"organization_id":10');
UPDATE history_changes SET data_json = REPLACE(data_json, '"organization":"mcm_telekom"', '"organization_id":11');
UPDATE history_changes SET data_json = REPLACE(data_json, '"organization":"markomnet_service"', '"organization_id":13');
UPDATE history_changes SET data_json = REPLACE(data_json, '"organization":"markomnet"', '"organization_id":14');
UPDATE history_changes SET data_json = REPLACE(data_json, '"organization":"markomnet_new"', '"organization_id":15');
UPDATE history_changes SET data_json = REPLACE(data_json, '"organization":""', '"organization_id":1');

UPDATE history_changes SET prev_data_json = REPLACE(prev_data_json, '"organization":"mcn_telekom"', '"organization_id":1');
UPDATE history_changes SET prev_data_json = REPLACE(prev_data_json, '"organization":"ooomcn"', '"organization_id":2');
UPDATE history_changes SET prev_data_json = REPLACE(prev_data_json, '"organization":"mcn"', '"organization_id":3');
UPDATE history_changes SET prev_data_json = REPLACE(prev_data_json, '"organization":"mcm"', '"organization_id":4');
UPDATE history_changes SET prev_data_json = REPLACE(prev_data_json, '"organization":"ooocmc"', '"organization_id":5');
UPDATE history_changes SET prev_data_json = REPLACE(prev_data_json, '"organization":"all4geo"', '"organization_id":6');
UPDATE history_changes SET prev_data_json = REPLACE(prev_data_json, '"organization":"all4net"', '"organization_id":7');
UPDATE history_changes SET prev_data_json = REPLACE(prev_data_json, '"organization":"wellstart"', '"organization_id":8');
UPDATE history_changes SET prev_data_json = REPLACE(prev_data_json, '"organization":"mcn_telekom_hungary"', '"organization_id":9');
UPDATE history_changes SET prev_data_json = REPLACE(prev_data_json, '"organization":"tel2tel_hungary"', '"organization_id":10');
UPDATE history_changes SET prev_data_json = REPLACE(prev_data_json, '"organization":"mcm_telekom"', '"organization_id":11');
UPDATE history_changes SET prev_data_json = REPLACE(prev_data_json, '"organization":"markomnet_service"', '"organization_id":13');
UPDATE history_changes SET prev_data_json = REPLACE(prev_data_json, '"organization":"markomnet"', '"organization_id":14');
UPDATE history_changes SET prev_data_json = REPLACE(prev_data_json, '"organization":"markomnet_new"', '"organization_id":15');
UPDATE history_changes SET prev_data_json = REPLACE(prev_data_json, '"organization":""', '"organization_id":1');



UPDATE client_contragent SET `name` = `name_full` WHERE `name` = '';
UPDATE client_contragent SET `name` = CONCAT('Контрагент ', id) WHERE `name` = '';
UPDATE client_contragent SET `name_full` = `name` WHERE `name_full` = '';