<?php

return [

    'voip_group_local' => 'helyi mobil',
    'voip_group_long_distance' => 'helyi',
    'voip_group_international' => 'nemzetközi',

    'voip_connection' => '{tariff} tarifájú IP telefonszolgáltatás',

    // Client "bill_rename1" = Előfizetési díj
    'voip_monthly_fee_per_number' => 'Előfizetési díj a {service} telefonszámra, {date_range}',

    // Client "bill_rename1" = Szerződés szerinti szolgáltatásnyújtás díja
    'voip_monthly_fee_per_number_custom' => '{service} telefonszámon nyújtott szolgáltatások {date_range}{by_agreement}',

    // Client "bill_rename1" = Előfizetési díj hangcsatornáért
    'voip_monthly_fee_per_line' => 'Előfizetési díj {lines_number} hangcsatornáért a {service}telefonszámhoz, {date_range}',
    // Yii::t plural not work
    // https://github.com/yiisoft/yii2/issues/4259

    // Client "bill_rename1" = Szerződés szerinti szolgáltatásnyújtás
    'voip_monthly_fee_per_line_custom' => 'Előfizetési díj {lines_number} hangcsatornáért a {service}telefonszámhoz {by_agreement}',
    // Yii::t plural not work
    // https://github.com/yiisoft/yii2/issues/4259

    'voip_overlimit' => 'Előfizetői díjcsomag szerinti limit túllépés a {service} telefonszámnál (helyi hívásirány) {date_range}',

    'voip_local_mobile_call_minpay' => 'Minimális befizetés a helyi mobil irányba kezdeményezett hívásokért a {service} telefonszámról, {date_range}',
    'voip_local_mobile_call_payment' => '{service} telefonszámról kezdeményezett helyi mobil irányú hívások forgalmi díja, {date_range}',

    'voip_long_distance_call_minpay' => 'Minimális befizetés a helyi irányba kezdeményezett hívásokért a {service} telefonszámról, {date_range}',
    'voip_long_distance_call_payment' => '{service} telefonszámról kezdeményezett helyi irányú hívások forgalmi díja, {date_range}',

    'voip_international_call_minpay' => 'Minimális befizetés a nemzetközi irányba kezdeményezett hívásokért a {service} telefonszámról, {date_range}',
    'voip_international_call_payment' => '{service} telefonszámról kezdeményezett nemzetközi irányú hívások forgalmi díja, {date_range}',

    'voip_group_minpay' => 'Minimális befizetés a ({group}) irányba kezdeményezett hívásokért a {service} telefonszámról, {date_range}',
    'voip_group_payment' => '{service} telefonszámról kezdeményezett ({group}) irányú hívások forgalmi díja, {date_range}',

    'voip_calls_minpay' => 'Minimális befizetés a {service} telefonszámról kezdeményezett hívásokért, {date_range}',
    // Client "bill_rename1" = Forgalmi díj
    'voip_calls_payment' => '{service} telefonszámról kezdeményezett hívások forgalmi díja, {date_range}',
    // Client "bill_rename1" = Serződés szerinti szolgáltatás-nyújtás
    'voip_calls_payment_custom' => '{service} telefonszámon nyújtott telefonhívás szolgáltatás {date_range}{by_agreement}',

    // Client "bill_rename1" = Forgalmi díj
    'voip_group_calls_payment' => '{service} telefonszámról indított hívások (helyi, helyközi, nemzetközi) forgalmi díja, {date_range}',
    // Client "bill_rename1" = Serződés szerinti szolgáltatás-nyújtás forgalmi díja
    'voip_group_calls_payment_custom' => '{service} telefonszámról indított hívások (helyi, helyközi, nemzetközi) forgalmi díja, {date_range}{by_agreement}',

    'voip_package_monfly_fee' => 'Percdíj csomag díja a {service} telefonszámhoz, {date_range}',

    'voip_sip_trunk_monfly_fee' => '{tariff} tarifa szerinti SIP trönk előfizetési díja, {date_range}',

    'voip_package_fee' => '"{tariff}" díjcsomag előfizetési díja {service} telefonszámhoz, {date_range}',
    'voip_package_payment' => '"{tariff}" díjcsomag forgalmi díja {service} telefonszámhoz,{date_range}',
    'voip_package_minpay' => '"{tariff}" díjcsomag minimális befizetése a {service} telefonszámhoz{date_range}',

    'voip_operator_trunk_orig' => 'Díjköteles forgalom operátor irányból a {service} trönkön, {date_range}',
    'voip_operator_trunk_term' => 'Díjköteles forgalom operátor irányba a {service} trönkön, {date_range}',
];
