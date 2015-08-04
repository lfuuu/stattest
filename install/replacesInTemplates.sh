replace '$client.signer_position' '$position' -- ./*
replace '$client.signer_name' '$fio' -- ./*
replace '$client.company' '$name' -- ./*
replace '$client.address_jur' '$address_jur' -- ./*
replace '$client.bank_properties' '$bank_properties' -- ./*
replace '$client.bik' '$bik' -- ./*
replace '$client.address_post_real' '$address_post_real' -- ./*
replace '$client.address_post' '$address_post' -- ./*
replace '$client.corr_acc' '$corr_acc' -- ./*
replace '$client.pay_acc' '$pay_acc' -- ./*
replace '$client.inn' '$inn' -- ./*
replace '$client.kpp' '$kpp' -- ./*
replace '$client.contact' '$contact' -- ./*
replace '$client.stamp' '$stamp' -- ./*
replace '$client.type' '$old_legal_type' -- ./*
replace '$client.address_connect' '$address_connect' -- ./*
replace '$client.id' '$account_id' -- ./*
replace '$client.bank_name' '$bank_name' -- ./*
replace '$client.contract.contragent.legal_type' '$legal_type' -- ./*
replace '$client.credit' '$credit' -- ./*

replace '$contract.contract_no' '$contract_no' -- ./*
replace '$contract.contract_date' '$contract_date' -- ./*
replace '$contract.contract_dop_date' '$contract_dop_date' -- ./*
replace '$contract.contract_dop_no' '$contract_dop_no' -- ./*

replace '$contact.email' '$emails' -- ./*
replace '$contact.phone' '$phones' -- ./*
replace '$contact.fax' '$faxes' -- ./*

replace '$client.firma' '$organization_firma' -- ./*
replace '$firm.director_post_' '$organization_director_post' -- ./*
replace '$firm.director_post' '$organization_director_post' -- ./*
replace '$firm.director_' '$organization_director' -- ./*
replace '$firm.director' '$organization_director' -- ./*
replace '$firm.name' '$organization_name' -- ./*
replace '$firm.address' '$organization_address' -- ./*
replace '$firm.inn' '$organization_inn' -- ./*
replace '$firm.kpp' '$organization_kpp' -- ./*
replace '$firm.kor_acc' '$organization_corr_acc' -- ./*
replace '$firm.bik' '$organization_bik' -- ./*
replace '$firm.bank' '$organization_bank' -- ./*
replace '$firm.phone' '$organization_phone' -- ./*
replace '$firm.email' '$organization_email' -- ./*
replace '$firm.acc' '$organization_pay_acc' -- ./*

replace '$firm_detail' '$firm_detail_block' -- ./*

replace '{$<a href="http://firm.name">firm.name</a>}' '<a href="http://{$organization_name}">{$organization_name}</a>' -- ./*



