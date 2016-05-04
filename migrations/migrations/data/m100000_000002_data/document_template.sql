INSERT INTO `document_template` VALUES (13,'Zakaz_Uslug',3,'<p><span style="font-size: 8pt;"><strong>Заказ на услуги&nbsp; № <strong>{$contract_dop_no}</strong></strong></span></p>
<p><span style="font-size: 8pt;"><strong>К договору №{$contract_no} от {$contract_date|mdate:\'"d" месяца Y\'} г.</strong></span></p>
<p><span style="font-size: 8pt;"><strong>заключенному между {$organization_name} и {$name_full}{if $contract_dop_no gt 1}</strong></span></p>
<p><span style="font-size: 8pt;"><strong>Прекращает действие Заказа №{$contract_dop_no-1}{/if}</strong></span></p>
<table style="width: 100%;" border="1" cellspacing="0" cellpadding="5">
<tbody>
<tr>
<td>
<p><span style="font-size: 8pt;"><em>Лицевой счет </em></span></p>
</td>
<td colspan="3">
<p><span style="font-size: 8pt;">{$account_id}</span></p>
</td>
</tr>
<tr>
<td>
<p><span style="font-size: 8pt;"><em>Адрес для доставки бухгалтерских документов</em></span></p>
</td>
<td colspan="3">
<p><span style="font-size: 8pt;">{$address_post_real}</span></p>
</td>
</tr>
<tr>
<td>
<p><span style="font-size: 8pt;"><em>E-mail для уведомлений и бухгалтерских документов</em></span></p>
</td>
<td colspan="3">
<p><span style="font-size: 10.6666669845581px;">{$emails}</span></p>
</td>
</tr>
<tr>
<td>
<p><span style="font-size: 8pt;"><em>Кредитный лимит, руб/мес</em></span></p>
</td>
<td colspan="3">
<p><span style="font-size: 8pt;">{if $credit == -1}-------------{else} {$credit} руб.&nbsp;{/if}</span></p>
</td>
</tr>
</tbody>
</table>
<p><span style="font-size: 8pt;"><strong><em>&nbsp;</em></strong></span></p>
<p><span style="font-size: 8pt;"><strong><em>Параметры Услуги:</em></strong></span></p>
<p><span style="font-size: 8pt;">&nbsp;&nbsp;&nbsp;{*#blank_zakaz#*}</span></p>
<p><span style="font-size: 8pt;">&nbsp;</span></p>
<p><span style="font-size: 8pt;">Услуги связи проверены представителем АБОНЕНТА, функционируют нормально и&nbsp;удовлетворяют требованиям Договора.</span></p>
<p><span style="font-size: 8pt;">&nbsp;</span></p>
<table style="width: 100%;">
<tbody>
<tr>
<td>
<p><span style="font-size: 8pt;">ОПЕРАТОР</span><br /> <br /> <br /> <br /><span style="font-size: 8pt;"> __________________________</span><br /><span style="font-size: 8pt;"> {$organization_director_post} {$organization_director}</span></p>
</td>
<td>
<p><span style="font-size: 8pt;">АБОНЕНТ</span><br /> <br /> <br /> <br /><span style="font-size: 8pt;"> ________________________</span><br /><span style="font-size: 8pt;"> {$position} {$fio}</span></p>
</td>
</tr>
</tbody>
</table>
<p><span style="font-size: 8pt;">&nbsp;</span></p>','blank');
INSERT INTO `document_template` VALUES (102,'Dog_UslugiSvayzi',3,'<p style="text-align: center;"><span style="font-size: 8pt;"><strong>Договор оказания услуг связи&nbsp; № {$contract_no}</strong></span></p>
<table width="100%">
<tbody>
<tr>
<td width="132">
<p><span style="font-size: 8pt;">г. Москва</span></p>
</td>
<td width="451">
<p style="text-align: right;"><span style="font-size: 8pt;">{$contract_date|mdate:\'"d" месяца Y\'}г.</span></p>
</td>
</tr>
</tbody>
</table>
<p><span style="font-size: 8pt;">{$name_full}, {if $legal_type == "legal"}именуемое в дальнейшем АБОНЕНТ, в качестве исполнительного органа и уполномоченного лица выступает {$position} {$fio}, действующий(ая) на основании Устава{else}именуемый(ая) в дальнейшем АБОНЕНТ,{/if} с одной стороны, и {$organization_name}, именуемое в дальнейшем ОПЕРАТОР,&nbsp;в качестве исполнительного органа и уполномоченного лица выступает {$organization_director_post} {$organization_director}, действующий(ая) на основании Устава, с другой стороны, именуемые в дальнейшем Стороны, заключили настоящий Договор о нижеследующем:</span></p>
<ol>
<li><span style="font-size: 8pt;"><strong> Определения</strong></span></li>
</ol>
<p><span style="font-size: 8pt;">1.1. Договор - настоящий документ с Приложениями и Заказами, а также все дополнения и изменения, подписанные Сторонами или принятые АБОНЕНТОМ в предусмотренном Договором порядке.</span></p>
<p><span style="font-size: 8pt;">1.2. Заказ - документ, подписываемый Сторонами в рамках данного Договора с целью приобретения АБОНЕНТОМ Услуг ОПЕРАТОРА, содержащий наименование Услуг, стоимость, метод расчетов (авансовый или кредитный с указанием размера кредитного лимита) и другую информацию, необходимую для реализации Заказа.</span></p>
<p><span style="font-size: 8pt;">1.3. Отчетный месяц - календарный месяц, в котором АБОНЕНТУ были оказаны Услуги.</span></p>
<p><span style="font-size: 8pt;">1.4. Лицевой счет&nbsp;- регистр аналитического счета в биллинговой системе ОПЕРАТОРА, предназначенный для отражения в учете операций по оказанию Услуг АБОНЕНТУ и их оплате.&nbsp;</span></p>
<p><span style="font-size: 8pt;">1.5. Баланс Лицевого счета &ndash; разность между суммой денежных средств, внесенных на Лицевой счет и суммой денежных средств, списанных с Лицевого счета.</span></p>
<p><span style="font-size: 8pt;">1.6. Авансовый метод&nbsp; расчетов &ndash; Услуги оказываются ОПЕРАТОРОМ на основании авансового платежа, произведенного АБОНЕНТОМ до начала пользования Услугами, при условии наличия положительно баланса денежных средств на&nbsp;Лицевом счете АБОНЕНТА в&nbsp;размере, достаточном для&nbsp;пользования Услугами.</span></p>
<p><span style="font-size: 8pt;">1.7. Кредитный метод расчетов &ndash; ОПЕРАТОР оказывает АБОНЕНТУ Услуги в кредит в размере суммы всех кредитных лимитов, указанных в Заказах (далее Общий кредитный лимит). Общий кредитный лимит равен разрешенному ОПЕРАТОРОМ минусу Баланса Лицевого счета АБОНЕНТА.</span></p>
<p><span style="font-size: 8pt;">1.8.&nbsp;Личный кабинет &ndash; индивидуальная страница АБОНЕНТА, создаваемая и поддерживаемая ОПЕРАТОРОМ на Интернет-сайте <a href="http://www.mcn.ru">www.mcn.ru</a>, содержащая статистическую информацию об объеме полученных Услуг и текущем Балансе Лицевого счета. На данной странице ОПЕРАТОРОМ размещаются счета на оплату Услуг, Универсальные передаточные документы или Акты и Счета-фактуры, информация о подключенных услугах, специальные уведомления ОПЕРАТОРА в адрес АБОНЕНТА, а также иная информация, размещенная в соответствии с условиями Договора.&nbsp;Действия, совершенные с использованием Личного кабинета Абонента, считаются совершенными от имени Абонента, при этом Абонент самостоятельно несет риск возможных&nbsp;неблагоприятных последствий изменений и настроек в Личном кабинете. Доступ к Личному кабинету Абонента осуществляется после регистрации Абонента на Интернет-сайте Оператора. &nbsp;</span></p>
<p>{if $organization_firma == \'mcn_telekom\' || $organization_firma == \'mcm_telekom\'}</p>
<p><span style="font-size: 8pt;">1.9. Услуги:</span></p>
<p>{if $organization_firma == \'mcn_telekom\'}</p>
<ul>
<li><span style="font-size: 8pt;">Услуги связи по передаче данных для целей передачи голосовой информации (Лицензия № 117137, сроком действия с 02.09.2011г. до 02.09.2016г.);</span></li>
<li><span style="font-size: 8pt;">Услуги внутризоновой связи (Лицензия № 117140, сроком действия с 02.09.2011г. до 02.09.2016г.)</span></li>
<li><span style="font-size: 8pt;">Услуги связи по передаче данных, за исключением услуг связи по передаче данных для целей передачи голосовой информации (Лицензия № 117139, сроком действия с 02.09.2011г. до 02.09.2016г.)</span></li>
<li><span style="font-size: 8pt;">Телематические услуги связи (Лицензия № 117138, сроком действия с 02.09.2011г. до 02.09.2016г.)</span></li>
<li><span style="font-size: 8pt;">Услуги местной телефонной связи, за исключением услуг местной телефонной связи с использованием таксофонов и средств коллективного доступа (Лицензия № 117141, сроком действия с 02.09.2011 до 02.09.2016г.)</span></li>
</ul>
<p>{else}</p>
<ul>
<li><span style="font-size: 8pt;">Услуги связи по передаче данных для целей передачи голосовой информации (Лицензия № 131874, сроком действия с 18.06.2015г. до 18.06.2020г.);</span></li>
<li><span style="font-size: 8pt;">Услуги связи по передаче данных, за исключением услуг связи по передаче данных для целей передачи голосовой информации (Лицензия № 131877, сроком действия с 18.06.2015г. до 18.06.2020г.)</span></li>
<li><span style="font-size: 8pt;">Телематические услуги связи (Лицензия № 131875, сроком действия с 18.06.2015г. до 18.06.2020г.)</span></li>
<li><span style="font-size: 8pt;">Услуги местной телефонной связи, за исключением услуг местной телефонной связи с использованием таксофонов и средств коллективного доступа (Лицензия № 131876, сроком действия с 18.06.2015г. до 18.06.2020г.)</span></li>
</ul>
<p>{/if}</p>
<p><span style="font-size: 8pt;">оказываются ОПЕРАТОРОМ АБОНЕНТУ в рамках отдельного Заказа к настоящему Договору. Описание, условия и порядок оказания каждой из Услуг, а также порядок взаимодействия Сторон в рамках оказания Услуг описываются в соответствующих Дополнительных соглашениях к Договору.</span></p>
<p>{/if}</p>
<ol start="2">
<li><span style="font-size: 8pt;"><strong> Предмет Договора</strong></span></li>
</ol>
<p><span style="font-size: 8pt;">В соответствии с имеющимися лицензиями, условиями настоящего Договора на основании Заказов ОПЕРАТОР оказывает АБОНЕНТУ Услуги, а АБОНЕНТ принимает и оплачивает их. Описание, порядок и условия оказания конкретных Услуг содержатся в соответствующих Дополнительных соглашениях к Договору.</span></p>
<ol start="3">
<li><span style="font-size: 8pt;"><strong> Срок действия, вступление в силу</strong></span></li>
</ol>
<p><span style="font-size: 8pt;">3.1. Договор вступает в силу после его подписания последней из Сторон (&laquo;Дата вступления Договора в силу&raquo;) и действует до&nbsp;конца текущего календарного года. Ежегодно, в случае если ни&nbsp;одна Сторона до 1-го декабря текущего года не&nbsp;оповестила другую о&nbsp;желании расторгнуть или пересмотреть Договор, его действие автоматически продлевается на&nbsp;следующий календарный&nbsp;год.</span></p>
<p><span style="font-size: 8pt;">3.2. Стороны признают равную юридическую силу собственноручной подписи и факсимиле подписи (воспроизведение личной подписи механическим способом) и пришли к соглашению о том, что настоящий Договор, Приложения, Дополнительные соглашения и иные документы, подписанные Сторонами путем простановки факсимиле подписи и печати, имеют силу оригинала.</span></p>
<ol start="4">
<li><span style="font-size: 8pt;"><strong> Оказание Услуг</strong></span></li>
</ol>
<p><span style="font-size: 8pt;">4.1. ОПЕРАТОР оказывает Услуги в соответствии с выданными Федеральной службой по надзору в сфере связи лицензиями, указанными в п. 1.9. настоящего Договора.</span></p>
<p><span style="font-size: 8pt;">4.2. Услуги предоставляются в соответствии с настоящим Договором 24 часа в сутки, 7 дней в неделю. Время реакции ОПЕРАТОРА на аварийную заявку АБОНЕНТА составляет 4 часа в рабочие дни с 9:00-18:00 по местному времени и 8 часов в остальное время.</span></p>
<p><span style="font-size: 8pt;">4.3. Адрес установки оконечного оборудования АБОНЕНТА, тип оборудования, способ выбора оператора услуг междугородной и международной телефонной связи, перечень обслуживаемых абонентских номеров АБОНЕНТА, а также иная необходимая для оказания Услуг информация, указываются в соответствующем Заказе.</span></p>
<table>
<tbody>
<tr>
<td>
<p><span style="font-size: 8pt;">4.4. В случае ухудшения качества Услуг, для получения консультаций, связанных с&nbsp;настройкой и&nbsp;использованием предоставляемых Услуг,&nbsp; АБОНЕНТ может обращаться в службу технической поддержки ОПЕРАТОРА:&nbsp;&nbsp;</span></p>
<ul>
<li><span style="font-size: 8pt;">Москва: +7 (495) 105-99-95</span></li>
<li><span style="font-size: 8pt;">Санкт-Петербург: +7 (812) 372-6999</span></li>
<li><span style="font-size: 8pt;">Краснодар: +7 (861) 204-0099</span></li>
<li><span style="font-size: 8pt;">Екатеринбург: +7 (343) 302-0099</span></li>
<li><span style="font-size: 8pt;">Новосибирск: +7 (383) 312-0099</span></li>
<li><span style="font-size: 8pt;">Самара: +7 (846) 215-0099</span></li>
<li><span style="font-size: 8pt;">Ростов-на-Дону: +7 (863) 309-0099</span></li>
<li><span style="font-size: 8pt;">Казань: +7 (843) 207-0099</span></li>
<li><span style="font-size: 8pt;">Нижний Новгород:+7 (831) 235-0099</span></li>
<li><span style="font-size: 8pt;">Владивосток: +7 (423) 206-0099</span></li>
</ul>
<p><span style="font-size: 8pt;">&nbsp;&nbsp;&nbsp;&nbsp; E-mail: <a href="mailto:support@mcn.ru">support@mcn.ru</a></span></p>
</td>
</tr>
</tbody>
</table>
<p><span style="font-size: 8pt;">&nbsp;</span></p>
<p><span style="font-size: 8pt;">4.5. Ежемесячно, не позднее 5 (пяти) рабочих дней со дня окончания отчетного месяца, ОПЕРАТОР оформляет и публикует в Личном кабинете АБОНЕНТА документы об оказании Услуг: Счета, Универсальные передаточные документы, Акты, Счета-фактуры и пр. Оператор изготавливает и доставляет Абоненту документы на бумажном носителе, заверенные печатью Оператора (Счета, Универсальные передаточные документы, Акты, Счета-фактуры, Акты сверки, Детализации счета и пр.), по заявкам Абонента.&nbsp;Стоимость доставки документов на бумажном носителе определяется согласно тарифам.&nbsp;</span></p>
<p><span style="font-size: 8pt;">4.6. ОПЕРАТОР вправе привлекать третьих лиц, в том числе, владеющих собственной или арендуемой сетью связи и имеющих необходимые лицензии на оказание услуг связи на территории РФ, для организации предоставления АБОНЕНТУ Услуг.</span></p>
<ol start="5">
<li><span style="font-size: 8pt;"><strong> Обязательства Сторон</strong></span></li>
</ol>
<p><span style="font-size: 8pt;"><strong>5.1.&nbsp;&nbsp;&nbsp;</strong> <strong>Стороны обязуются:</strong></span></p>
<p><span style="font-size: 8pt;">5.1.2. При осуществлении деятельности по Договору применять только лицензированное программное обеспечение и исправно работающее оборудование, сертифицированное в установленном в Российской Федерации порядке.</span></p>
<p><span style="font-size: 8pt;">5.1.3. Самостоятельно оплачивать все расходы, связанные с выполнением своих обязательств по Договору, если иное прямо не предусмотрено в Договоре или не согласовано с другой Стороной иным способом.</span></p>
<p><span style="font-size: 8pt;">5.1.4. Соблюдать режим конфиденциальности в отношении информации, обозначенной передающей Стороной как &laquo;Конфиденциальная&raquo; и признанной таковой в соответствии с действующим законодательством РФ.</span></p>
<p><span style="font-size: 8pt;"><strong>5.2.&nbsp;&nbsp;&nbsp;</strong> <strong>ОПЕРАТОР обязуется:</strong></span></p>
<p><span style="font-size: 8pt;">5.2.2. Обеспечивать ежедневное и круглосуточное функционирование оборудования, к которому подключается АБОНЕНТ, за исключением промежутков времени для проведения профилактических и ремонтных работ, а также времени, необходимого для оперативного устранения отказов или повреждений линейного, кабельного или станционного оборудования.</span></p>
<p><span style="font-size: 8pt;">5.2.3. Проводить профилактические и регламентные работы в часы наименьшей нагрузки, а также информировать АБОНЕНТА о дате, времени и продолжительности названных работ не менее чем за 24 часа до даты их проведения.</span></p>
<p><span style="font-size: 8pt;">5.2.4. Обеспечивать выполнение требований по соблюдению тайны связи в соответствии с Федеральным Законом &laquo;О связи&raquo; от 7 июля 2003 г . №126-ФЗ (далее &laquo;ФЗ &laquo;О связи&raquo;).</span></p>
<p><span style="font-size: 8pt;">5.2.5. При оказании Услуг обеспечивать параметры качества в соответствии с нормативными документами отрасли &laquo;Связь&raquo;.</span></p>
<p><span style="font-size: 8pt;">5.2.6. Предоставлять АБОНЕНТУ возможность доступа к Личному кабинету. Доступ к Личному кабинету предоставляется АБОНЕНТУ с момента регистрации в Личном кабинете. В случае временного приостановления оказания Услуг, Личный кабинет остается доступным для АБОНЕНТА в течение срока действия Договора.</span></p>
<p><span style="font-size: 8pt;"><strong>5.3.&nbsp;&nbsp;&nbsp;</strong> <strong>АБОНЕНТ обязуется:</strong></span></p>
<p><span style="font-size: 8pt;">5.3.1. Принимать оказанные ОПЕРАТОРОМ Услуги в соответствии с условиями Договора и Дополнительных соглашений к нему.</span></p>
<p><span style="font-size: 8pt;">5.3.2. Полностью и своевременно производить оплату Услуг в соответствии с условиями Договора и Дополнительных соглашений к нему.</span></p>
<p><span style="font-size: 8pt;">5.3.3. Оперативно предоставлять по запросу ОПЕРАТОРА всю информацию, в том числе техническую, которая может потребоваться ОПЕРАТОРУ для реализации Заказа.</span></p>
<p><span style="font-size: 8pt;">5.3.4. В случае необходимости, выделить ОПЕРАТОРУ место для установки оборудования в помещении по адресу, указанному в Заказе к Договору и обеспечить к нему доступ специалистов ОПЕРАТОРА; обеспечить получение всех необходимых разрешений и согласований от владельца территории (помещения), на которой расположено оборудование АБОНЕНТА, на проведение работ по прокладке кабеля, строительству кабельной канализации и организации кабельного ввода, а также по размещению и электропитанию оборудования.</span></p>
<p><span style="font-size: 8pt;">5.3.5. В&nbsp;период временного прекращения предоставления Услуг АБОНЕНТУ по&nbsp;причинам, изложенным в&nbsp;п.&nbsp;п.&nbsp;6.10., 6.16. Договора, оплачивать ежемесячную абонентскую плату в&nbsp;соответствии с&nbsp;действующим тарифным планом.</span></p>
<p><span style="font-size: 8pt;">5.3.6. Самостоятельно контролировать Баланс Лицевого счета, получение счетов и уведомлений ОПЕРАТОРА в Личном кабинете.</span></p>
<p><span style="font-size: 8pt;">5.3.7. Предоставлять Оператору полную, достоверную и актуальную информацию о себе, поддерживать актуальность и полноту данной информации посредством Личного кабинета Абонента.</span></p>
<ol start="6">
<li><span style="font-size: 8pt;"><strong> Стоимость Услуг и условия оплаты</strong></span></li>
</ol>
<p><span style="font-size: 8pt;">6.1. Ежемесячная стоимость Услуг определяется в соответствии с Заказом и действующими тарифами ОПЕРАТОРА. Тарифы, действующие на момент подписания Договора и/или Дополнительных соглашений к нему, приведены в соответствующих Дополнительных соглашениях к настоящему Договору.</span></p>
<p><span style="font-size: 8pt;">6.2. ОПЕРАТОР вправе в одностороннем порядке изменить действующие тарифы АБОНЕНТА и сроки оплаты Услуг с предварительным уведомлением АБОНЕНТА за 10 (десять) календарных дней до даты введения в действие таких изменений. Уведомление АБОНЕНТА осуществляется по электронной почте и /или путем публикации информации на&nbsp; Интернет-сайте ОПЕРАТОРА&nbsp;<a href="http://www.mcn.ru">www.mcn.ru</a> (Свидетельство о регистрации СМИ Интернет-сайт <a href="http://www.mcn.ru">www.mcn.ru</a>: Эл № ФС77-61463).</span></p>
<p><span style="font-size: 8pt;">6.3. В случае несогласия АБОНЕНТА с изменением тарифов ОПЕРАТОРА и изъявлении желания расторгнуть Договор, АБОНЕНТ обязан оплатить счета за оказанные Услуги по Договору до момента расторжения Договора.</span></p>
<p><span style="font-size: 8pt;">6.4. АБОНЕНТ вправе перейти на любой другой действующий тарифный план ОПЕРАТОРА, доступный для существующих клиентов, предварительно уведомив об этом ОПЕРАТОРА по электронной почте, факсу или через Личный кабинет. Перевод АБОНЕНТА на новый тарифный план осуществляется с первого числа календарного месяца при условии наличия технической возможности и получения ОПЕРАТОРОМ соответствующего уведомления от АБОНЕНТА с указанием даты перевода и названия выбранного тарифного плана не позднее, чем за 1(один) рабочий день до окончания предыдущего календарного месяца.</span></p>
<p><span style="font-size: 8pt;">6.5. Ежемесячно, не позднее 5 (пяти) рабочих дней со дня начала отчетного месяца, ОПЕРАТОР выставляет счет, размещает его в Личном кабинете, а также направляет АБОНЕНТУ по электронной почте&nbsp;. С момента размещения счета в Личном кабинете, в соответствии п. 5.3.6. Договора, счет считается полученным АБОНЕНТОМ, за исключением случаев недоступности Личного кабинета по вине ОПЕРАТОРА.&nbsp;</span></p>
<p><span style="font-size: 8pt;">6.6. В случае заключения Сторонами нескольких Заказов на одну Услугу и /или нескольких Заказов на разные виды Услуг для оплаты ежемесячной стоимости Услуг ОПЕРАТОР выставляет единый счет.</span></p>
<p><span style="font-size: 8pt;">6.7. По инициативе АБОНЕНТА или ОПЕРАТОРА, ОПЕРАТОР имеет право выделить АБОНЕНТУ несколько Лицевых счетов для ведения раздельного учета по оплате разных Заказов на одну Услугу и /или нескольких Заказов на разные виды Услуг. Лицевые счета указываются в соответствующих им Заказах. При заключении Сторонами нескольких Заказов на одну Услугу и /или нескольких Заказов на разные виды Услуг, в случае ведения учета по их оплате в рамках единого Лицевого счета, для расчетов по данным Заказам и Услугам АБОНЕНТА применяется единый метод расчетов: либо авансовый, либо кредитный.</span></p>
<p><span style="font-size: 8pt;">6.8. Оплата счетов за Услуги производится в российских рублях.&nbsp; <br /></span></p>
<p><span style="font-size: 8pt;">6.9.&nbsp;АБОНЕНТ оплачивает Услуги на&nbsp;основании счетов, выставляемых ОПЕРАТОРОМ согласно пункту 6.5 настоящего Договора, в течение 10 рабочих дней с момента публикации счета в Личном кабинете. Оплата считается произведённой в момент зачисления денежных средств на расчетный счет ОПЕРАТОРА. Расходы по переводу денежных средств относятся на счет АБОНЕНТА. В случае применения Авансового метода расчетов, при условии отсутствия в момент списания денежных средств за оказанные ОПЕРАТОРОМ Услуги на Лицевом счете АБОНЕНТА суммы, достаточной для оплаты Услуг, оказание Услуг временно приостанавливается. В случае применения Кредитного метода расчетов, при условии, что на момент списания денежных средств за оказанные ОПЕРАТОРОМ Услуги, суммы Баланса Лицевого счета и Общего кредитного лимита АБОНЕНТА недостаточно для оплаты Услуг, оказание Услуг временно приостанавливается.</span></p>
<p><span style="font-size: 8pt;">6.10. В случае нарушения обязательств по оплате, указанных в п. 5.3.2. настоящего Договора, в соответствии с п. 6.9. настоящего Договора, ОПЕРАТОР имеет право временно приостановить оказание Услуг до&nbsp;полного погашения задолженности АБОНЕНТОМ. В случае приостановления оказания Услуг по причине наличия задолженности, ОПЕРАТОР возобновляет оказание Услуг АБОНЕНТУ в течение следующего рабочего дня со дня поступления денежных средств на расчетный счет ОПЕРАТОРА.</span></p>
<p><span style="font-size: 8pt;">6.11. &nbsp;В случае несогласия с&nbsp;объемом предоставленной Услуги согласно счетам ОПЕРАТОРА, АБОНЕНТ в&nbsp;течение 5&nbsp;(пяти) рабочих дней с&nbsp;момента получения счета должен представить ОПЕРАТОРУ письменную претензию, в&nbsp;противном случае Услуга считается выполненной. ОПЕРАТОР в&nbsp;течение 10&nbsp;рабочих дней должен представить ответ на&nbsp;претензию.</span></p>
<p><span style="font-size: 8pt;">6.12. В случае обнаружения ошибок в выставленном ОПЕРАТОРОМ счете соответствующая корректировка проводится в счете за последующий отчетный месяц.</span></p>
<p><span style="font-size: 8pt;">6.13. При осуществлении расчетов по настоящему Договору АБОНЕНТ обязан указывать в платежных документах следующие сведения: наименование плательщика; наименование получателя платежа и его банковские реквизиты, ИНН, КПП; наименование банка получателя; сумму платежа; документы, на основании которых производится платеж (договор от &hellip;. № &hellip;.; счет&nbsp; от &hellip;. № &hellip;.); вид платежа (единовременная плата, ежемесячная плата, неустойка (пеня, убытки); период, за который производится платеж). В случае если АБОНЕНТ не указал или ненадлежащим образом указал в платежных документах сведения о расчетном периоде, за который произведен платеж, период определяется ОПЕРАТОРОМ самостоятельно. При этом, если существует задолженность предыдущего периода, то ОПЕРАТОР засчитывает вышеуказанную плату в счет погашения задолженности предыдущего периода (с учетом положений п. 6.14. Договора).</span></p>
<p><span style="font-size: 8pt;">6.14. При неполной оплате счета ОПЕРАТОР вправе по своему усмотрению засчитывать осуществленный АБОНЕНТОМ платеж пропорционально в счет оплаты каждого Заказа или в счет полной оплаты отдельных Заказов &nbsp;в рамках одного Лицевого счета.</span></p>
<p><span style="font-size: 8pt;">6.15. В&nbsp;случае невозможности оказания качественных Услуг, наступившей из-за повреждений оборудования ОПЕРАТОРА, ежемесячный абонентский платеж за&nbsp;время невозможности качественного предоставления Услуг уменьшается пропорционально времени неисправности.</span></p>
<p><span style="font-size: 8pt;">6.16. В&nbsp;случаях отказа оборудования АБОНЕНТА или отказа оборудования ОПЕРАТОРА вследствие ненадлежащей эксплуатации его АБОНЕНТОМ, неисправности исправляются ОПЕРАТОРОМ за&nbsp;счет АБОНЕНТА. ОПЕРАТОР не&nbsp;несет ответственности за&nbsp;такие ситуации и&nbsp;ежемесячный абонентский платеж не&nbsp;уменьшается.</span></p>
<p><span style="font-size: 8pt;">6.17. Если на момент прекращения действия Договора и оплаты всех оказанных ОПЕРАТОРОМ Услуг Баланс Лицевого счета имеет положительное значение, ОПЕРАТОР возвращает неизрасходованный остаток денежных средств на основании письменного заявления об их возврате, направленного АБОНЕНТОМ вместе с уведомлением о расторжении Договора.</span></p>
<p><span style="font-size: 8pt;">6.18. Счет, выставляемый согласно пункту 6.5. настоящего Договора, включает в себя ежемесячные абонентские платежи за услуги, оказываемые АБОНЕНТУ в Отчетном месяце. В случае неоплаты счета в срок, установленный настоящим Договором, ОПЕРАТОР вправе приостановить оказание Услуг согласно пункту 6.9. настоящего Договора. При этом ежемесячные абонентские платежи за период приостановки оказания Услуг начисляются в полном объеме и подлежат оплате АБОНЕНТОМ, работающим по кредитному или авансовому методу оплаты.</span></p>
<ol start="7">
<li><span style="font-size: 8pt;"><strong> Ответственность Сторон</strong></span></li>
</ol>
<p><span style="font-size: 8pt;">7.1. За неисполнение либо ненадлежащее исполнение обязательств по Договору Стороны несут ответственность в соответствии с действующим законодательством Российской Федерации.</span></p>
<p><span style="font-size: 8pt;">7.2. Стороны не несут ответственности в случаях действия обстоятельств непреодолимой силы, а именно чрезвычайных и непредотвратимых обстоятельств: стихийных бедствий (землетрясений, наводнений и т.д.), обстоятельств общественной жизни (военных действий, крупномасштабных забастовок, эпидемий, аварий на энергоснабжающих предприятиях и т.д.) и запретительных мер государственных органов. О наступлении таких обстоятельств Стороны письменно информируют друг друга в течение пяти дней с момента их наступления.</span></p>
<p><span style="font-size: 8pt;">7.3. ОПЕРАТОР не несет ответственности перед АБОНЕНТОМ и третьими лицами за прямые и/или косвенные убытки, понесённые АБОНЕНТОМ и/или третьими лицами, посредством использования Услуг или получения доступа к ним.</span></p>
<p><span style="font-size: 8pt;">7.4. ОПЕРАТОР не несет ответственности в случае сбоев программного обеспечения и/или появление дефектов в оборудовании АБОНЕНТА или любых третьих лиц.</span></p>
<p><span style="font-size: 8pt;">7.5. ОПЕРАТОР не&nbsp;отвечает за&nbsp;содержание информации, передаваемой и&nbsp;получаемой АБОНЕНТОМ, за&nbsp;исключением случая собственной информации ОПЕРАТОРА.</span></p>
<ol start="8">
<li><span style="font-size: 8pt;"><strong> Расторжение Договора</strong></span></li>
</ol>
<p><span style="font-size: 8pt;">8.1. В&nbsp;случае неоплаты АБОНЕНТОМ выставленного ОПЕРАТОРОМ счета до конца месяца, следующего за отчетным, ОПЕРАТОР имеет право произвести окончательное отключение Услуг, при этом настоящий Договор считается расторгнутым ОПЕРАТОРОМ в одностороннем порядке.</span></p>
<p><span style="font-size: 8pt;">8.2. АБОНЕНТ вправе в одностороннем порядке расторгнуть настоящий Договор, при условии оплаты ОПЕРАТОРУ всех причитающихся сумм по Договору в соответствии со ст. 782 ГК РФ и получения ОПЕРАТОРОМ письменного уведомления от АБОНЕНТА о намерении расторгнуть Договор за 30 (тридцать) календарных дней до даты его расторжения.</span></p>
<p><span style="font-size: 8pt;">8.3. Обязательства Сторон по п. 5.1.4. настоящего Договора, продолжают действовать и после истечения срока действия или расторжения Договора.</span></p>
<ol start="9">
<li><span style="font-size: 8pt;"><strong> Другие Положения</strong></span></li>
</ol>
<p><span style="font-size: 8pt;">9.1. Любое изменение Договора оформляется в виде Дополнительного соглашения к Договору, которое вступает в силу только после его подписания Сторонами, если иной порядок изменения не предусмотрен положениями настоящего Договора (включая Приложения, Дополнительные соглашения и Заказы), либо действующего законодательства РФ.</span></p>
<p><span style="font-size: 8pt;">9.2. Неправильность, недействительность, невыполнимость или незаконность какого-либо положения Договора не влияет на действительность или выполнимость любого другого из остальных положений Договора.</span></p>
<p><span style="font-size: 8pt;">9.3. Если в Дополнительных соглашениях к Договору условия предоставления Услуг отличаются от условий предоставления Услуг, предусмотренных в Договоре, то положения Дополнительных соглашений будут превалировать над текстом Договора.</span></p>
<p><span style="font-size: 8pt;">9.4. Все споры, разногласия или требования, возникающие по настоящему Договору или в связи с ним, подлежат разрешению путем переговоров Сторон. В случае если Стороны не пришли к соглашению по спорному вопросу, спор подлежит рассмотрению в Арбитражном суде г. Москвы в соответствии с действующим законодательством РФ.</span></p>
<p><span style="font-size: 8pt;">9.5. В&nbsp;случае изменения адреса доставки, электронной почты, телефона и /или&nbsp;факса, каждая из&nbsp;Сторон обязуется в&nbsp;течение 5&nbsp;(пяти) дневного срока известить об&nbsp;этом другую Сторону по электронной почте и /или факсу.</span></p>
<p><span style="font-size: 8pt;">9.6. В случае изменения организационно-правовой формы юридического лица, фирменного наименования, изменения реквизитов компании, изменения адреса предоставления услуг, АБОНЕНТ обязан письменно уведомить об этом ОПЕРАТОРА не менее чем за 1 (один) рабочий день до конца текущего отчетного периода.&nbsp;</span></p>
<p><span style="font-size: 8pt;">9.7. АБОНЕНТ вправе переоформить Услуги на другое юридическое лицо, письменно уведомив о своем намерении ОПЕРАТОРА. За переоформление договора ОПЕРАТОР взимает единовременную плату согласно действующему тарифу.</span></p>
<p><span style="font-size: 8pt;">9.8. Ни одна из Сторон не может передавать свои права и обязанности по данному договору какой-либо третьей стороне без согласия другой Стороны.</span></p>
<p><span style="font-size: 8pt;">9.9. Договор, все Приложения и Дополнительные соглашения к нему, включая Заказы, составляют единое целое. Настоящий Договор подписан Сторонами в двух экземплярах, имеющих одинаковую юридическую силу, по одному для каждой из Сторон.</span></p>
<ol start="10">
<li><span style="font-size: 8pt;"><strong> Адреса и реквизиты сторон</strong>:</span></li>
</ol>
<table style="width: 100%;">
<tbody>
<tr>
<td>
<p><span style="font-size: 8pt;">ОПЕРАТОР: {$firm_detail_block}</span></p>
</td>
<td>
<p style="font-size: 8pt;">АБОНЕНТ: {$payment_info}</p>
</td>
</tr>
</tbody>
</table>
<ol start="11">
<li><span style="font-size: 8pt;"><strong>Подписи сторон:</strong></span></li>
</ol>
<table style="width: 100%;">
<tbody>
<tr>
<td>
<p><span style="font-size: 8pt;">ОПЕРАТОР</span></p>
<p><span style="font-size: 8pt;">__________________________</span><br /><span style="font-size: 8pt;"> {$organization_director_post} {$organization_director}</span></p>
</td>
<td>
<p><span style="font-size: 8pt;">АБОНЕНТ</span></p>
<p><span style="font-size: 8pt;">__________________________</span><br /><span style="font-size: 8pt;">{if ($legal_type) == "legal"}{$position} {$fio}{else}{$name_full}{/if}</span></p>
</td>
</tr>
</tbody>
</table>','contract');


INSERT INTO `document_template` VALUES (133,'Договор Вегрия',3,'<h1>Egyedi előfizetői szerződ&eacute;s</h1>
<p><span style="font-size: 10pt;">Jelen<strong> Egyedi Előfizetői Szerződ&eacute;s </strong>l&eacute;trej&ouml;tt</span></p>
<p><span style="font-size: 10pt;"><strong>a {$organization_name} (</strong>ad&oacute;sz&aacute;m<strong>: </strong>12773246-2-43, bejegyzett c&iacute;m: Budapest 1114, Kemenes u., 8, f&eacute;lemelet 3<strong>), mint MCNtelecom Szolg&aacute;ltat&oacute; &eacute;s </strong></span></p>
<p><span style="font-size: 10pt;">&nbsp;</span></p>
<p><span style="font-size: 10pt;"><strong>{$name_full} (</strong>ad&oacute;sz&aacute;m: {$inn} bejegyzett c&iacute;m: {$address_jur}),<strong> mint Előfizető </strong>k&ouml;z&ouml;tt<strong>.</strong></span></p>
<p><span style="font-size: 10pt;">&nbsp;</span></p>
<ol>
<li><span style="font-size: 10pt;"><strong> A Szolg&aacute;ltat&aacute;s le&iacute;r&aacute;sa.</strong> Jelen szerződ&eacute;s t&aacute;rgya a telep&iacute;tett telefon alk&ouml;zpont rendszer&eacute;n &eacute;s informatikai eszk&ouml;z&ouml;k&ouml;n IP alap&uacute; (SIP) helyi, belf&ouml;ldi &eacute;s mobil, valamint nemzetk&ouml;zi vezet&eacute;kes &eacute;s mobil ir&aacute;nyokra ig&eacute;nybe vehető, PSTN-el egyen&eacute;rt&eacute;kű szolg&aacute;ltat&aacute;s (h&iacute;v&aacute;sind&iacute;t&aacute;s &eacute;s v&eacute;gződtet&eacute;s, a tov&aacute;bbiakban "Szolg&aacute;ltat&aacute;s") ny&uacute;jt&aacute;sa az Előfizető r&eacute;sz&eacute;re, az eszk&ouml;z&ouml;k rendszeres karbantart&aacute;sa, &uuml;gyeleti rendszer biztos&iacute;t&aacute;sa az esetleges meghib&aacute;sod&aacute;sok bejelent&eacute;se a fogad&aacute;sra, valamint a telefon alk&ouml;zpont rendszer &eacute;n informatikai eszk&ouml;z&ouml;k hib&aacute;inak elh&aacute;r&iacute;t&aacute;sa. A Szolg&aacute;ltat&aacute;s v&eacute;szh&iacute;v&aacute;sok c&eacute;lj&aacute;ra nem alkalmas, noha a seg&eacute;lyh&iacute;v&oacute; sz&aacute;mok h&iacute;vhat&oacute;ak, ugyanis a szolg&aacute;ltat&aacute;s &aacute;ramkimarad&aacute;s &eacute;s az internetkapcsolat hib&aacute;ja eset&eacute;n nem műk&ouml;dik.</span></li>
</ol>
<p><span style="font-size: 10pt;">Az Előfizető kijelenti, hogy elolvasta &eacute;s elfogadja az &Aacute;ltal&aacute;nos Szerződ&eacute;si Felt&eacute;teleket (&Aacute;SZF). &Aacute;SZF a jelen szerződ&eacute;s r&eacute;sz&eacute;t k&eacute;pezi. Az Előfizető tov&aacute;bb&aacute; kijelenti, hogy megismerte a szolg&aacute;ltat&aacute;sny&uacute;jt&aacute;s felt&eacute;teleivel, a Szolg&aacute;ltat&oacute; &aacute;rjegyz&eacute;kkel &eacute;s a Szolg&aacute;ltat&oacute; &aacute;ltal felk&iacute;n&aacute;lt akci&oacute;s aj&aacute;nlatokkal azok teljes terjedelm&eacute;ben. Mindenkori &Aacute;SZF a Szolg&aacute;ltat&oacute; honlapj&aacute;n &eacute;s irod&aacute;j&aacute;ban &eacute;rhető el.</span></p>
<ol start="2">
<li><span style="font-size: 10pt;"><strong> &Uuml;gyf&eacute;lszolg&aacute;lat &eacute;s hibabejelent&eacute;s</strong> el&eacute;rhetős&eacute;ge: info@mcntele.com; <a href="http://www.mcntelel.com/">www.mcntelel.com</a>; &Uuml;gyf&eacute;lszolg&aacute;lat &eacute;s hibabejelent&eacute;s: Budapest, Kemenes utca 8. f&eacute;lemelet 3.; Telefon: +36 (1) 490 0999; Nyitva tart&aacute;s: munkanapokon 09-16h; e-mail: info@mcntele.com.</span></li>
</ol>
<p><span style="font-size: 10pt;">Előfizető kijelenti, hogy a Szolg&aacute;ltat&oacute; &Aacute;ltal&aacute;nos Szerződ&eacute;si Felt&eacute;teleit (&bdquo;&Aacute;SZF&rdquo;) a megismerte &eacute;s annak felt&eacute;teleit elfogadja. Az &Aacute;SZF mindenkor a szerződ&eacute;s r&eacute;sz&eacute;t k&eacute;pezi, &eacute;s egyben kijelenti, hogy a Szolg&aacute;ltat&oacute; akci&oacute;s felt&eacute;teleit, vonatkoz&oacute; d&iacute;jszab&aacute;s&aacute;t &eacute;s a szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tel&eacute;re vonatkoz&oacute; szerződ&eacute;si felt&eacute;teleket teljes k&ouml;rűen megismerte &eacute;s az &Aacute;SZF kivonatot &aacute;tvette. A mindenkor hat&aacute;lyos &Aacute;SZF el&eacute;rhető a Szolg&aacute;ltat&oacute; weboldal&aacute;n &eacute;s &uuml;gyf&eacute;lszolg&aacute;lat&aacute;n.</span></p>
<ol start="3">
<li><span style="font-size: 10pt;"><strong> A Szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tele &eacute;s haszn&aacute;lata</strong></span></li>
</ol>
<p><span style="font-size: 10pt;">A Szolg&aacute;ltat&aacute;s Magyarorsz&aacute;g ter&uuml;let&eacute;n vehető ig&eacute;nybe. A Szolg&aacute;ltat&oacute; az ig&eacute;nyt abban az esetben el&eacute;g&iacute;ti ki, ha az előfizetői v&eacute;gberendez&eacute;s telep&iacute;t&eacute;s&eacute;nek nincsenek műszaki, jogi, hat&oacute;s&aacute;gi korl&aacute;tai, a telep&iacute;t&eacute;s &eacute;sszerű k&ouml;lts&eacute;ghat&aacute;rok mellett megval&oacute;s&iacute;that&oacute; &eacute;s az Előfizetőnek nincs lej&aacute;rt tartoz&aacute;sa a Szolg&aacute;ltat&oacute;val szemben. A Szolg&aacute;ltat&aacute;st az Előfizető k&ouml;teles rendeltet&eacute;sszerűen haszn&aacute;lni.</span></p>
<ol start="4">
<li><span style="font-size: 10pt;"><strong> A Szolg&aacute;ltat&aacute;s l&eacute;tes&iacute;t&eacute;s&eacute;vel &eacute;s műk&ouml;dtet&eacute;s&eacute;vel kapcsolatos előfizetői k&ouml;telezetts&eacute;gek</strong></span></li>
</ol>
<p><span style="font-size: 10pt;">4.1. Az Előfizető k&ouml;teles gondoskodni arr&oacute;l, hogy a Szolg&aacute;ltat&oacute; a ki&eacute;p&iacute;t&eacute;s szempontj&aacute;b&oacute;l &eacute;rintett ingatlanra bejusson &eacute;s a Szolg&aacute;ltat&aacute;s ny&uacute;jt&aacute;s&aacute;hoz sz&uuml;ks&eacute;ges berendez&eacute;seit d&iacute;jmentesen elhelyezhesse. Az Előfizető k&ouml;teless&eacute;ge az ingatlan tulajdonos&aacute;t&oacute;l &iacute;r&aacute;sos hozz&aacute;j&aacute;rul&aacute;st beszerezni, amennyiben az sz&uuml;ks&eacute;ges. Az Előfizető az eszk&ouml;z&ouml;k elhelyez&eacute;s&eacute;hez sz&uuml;ks&eacute;ges helyet ingyenesen biztos&iacute;tja a Szolg&aacute;ltat&oacute; sz&aacute;m&aacute;ra. A helyi informatikai rendszeren a Szolg&aacute;ltat&aacute;s műk&ouml;d&eacute;s&eacute;hez sz&uuml;ks&eacute;ges helyi h&aacute;l&oacute;zat ki&eacute;p&iacute;t&eacute;se nem k&eacute;pezi r&eacute;sz&eacute;t jelen szerződ&eacute;snek.</span></p>
<p><span style="font-size: 10pt;">A Szolg&aacute;ltat&aacute;s műk&ouml;d&eacute;s&eacute;nek felt&eacute;tele, a műk&ouml;dő internet kapcsolat, amelynek biztos&iacute;t&aacute;sa az Előfizető k&ouml;telezetts&eacute;ge. Amennyiben b&aacute;rmilyen okb&oacute;l ez nem &aacute;ll a rendelkez&eacute;sre, az ebből ad&oacute;d&oacute; szolg&aacute;ltat&aacute;s-kies&eacute;s&eacute;rt a Szolg&aacute;ltat&oacute;t semmilyen felelőss&eacute;g nem terheli. A Szolg&aacute;ltat&oacute; &aacute;ltal a Szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tel&eacute;hez biztos&iacute;tott valamennyi eszk&ouml;z a Szolg&aacute;ltat&oacute; tulajdon&aacute;t k&eacute;pezi, annak kifog&aacute;stalan &aacute;llapotban t&ouml;rt&eacute;nő megőrz&eacute;s&eacute;&eacute;rt, rendeltet&eacute;sszerű haszn&aacute;lat&aacute;&eacute;rt, a Szerződ&eacute;s megszűn&eacute;s&eacute;vel egyidejűleg t&ouml;rt&eacute;nő visszaszolg&aacute;ltat&aacute;s&aacute;&eacute;rt az Előfizető felel.</span></p>
<p><span style="font-size: 10pt;">Az Előfizető tulajdon&aacute;ban l&eacute;vő eszk&ouml;z&ouml;k&ouml;n a Szolg&aacute;ltat&oacute; semmilyen be&aacute;ll&iacute;t&aacute;st nem v&eacute;gez, &nbsp;ezen eszk&ouml;z&ouml;k&ouml;n amennyiben sz&uuml;ks&eacute;g van &nbsp;Szolg&aacute;ltat&aacute;shoz konfigur&aacute;ci&oacute;ra, vagy egy&eacute;b m&oacute;dos&iacute;t&aacute;sra, cser&eacute;re, ez minden esetben az Előfizető felelőss&eacute;ge &eacute;s feladata.</span></p>
<p><span style="font-size: 10pt;">4.2. Amennyiben a Szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tel&eacute;hez sz&uuml;ks&eacute;ges műszaki, jogi, hat&oacute;s&aacute;gi felt&eacute;telek teljes&uuml;lnek &eacute;s a telep&iacute;t&eacute;s &eacute;sszerű k&ouml;lts&eacute;ghat&aacute;rok mellett megval&oacute;s&iacute;that&oacute;, akkor a Szolg&aacute;ltat&oacute; a Szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tel&eacute;nek lehetős&eacute;g&eacute;t az &eacute;rv&eacute;nyes Szerződ&eacute;s k&eacute;zhezv&eacute;tel&eacute;től sz&aacute;m&iacute;tott 30 (harminc) napon bel&uuml;l biztos&iacute;tja. Szerződ&eacute;s a Felek k&ouml;z&ouml;tt a Szolg&aacute;ltat&aacute;s műszaki megval&oacute;sul&aacute;s&aacute;val j&ouml;n l&eacute;tre &eacute;s l&eacute;p hat&aacute;lyba. A Szolg&aacute;ltat&aacute;s ny&uacute;jt&aacute;sa a sz&uuml;ks&eacute;ges IP kapcsolat &uuml;zemel&eacute;se első napja ut&aacute;ni munkanapon kezdődik. Amennyiben a Szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tel&eacute;hez sz&uuml;ks&eacute;ges műszaki, jogi, hat&oacute;s&aacute;gi felt&eacute;telek nem teljes&uuml;lnek, &eacute;s a telep&iacute;t&eacute;s az &eacute;sszerű k&ouml;lts&eacute;ghat&aacute;rok mellett nem megval&oacute;s&iacute;that&oacute;, akkor a Szolg&aacute;ltat&oacute; a Szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tel&eacute;nek lehetős&eacute;g&eacute;t nem biztos&iacute;tja, a Szerződ&eacute;s nem j&ouml;n l&eacute;tre. Indokolt esetben a Szolg&aacute;ltat&oacute; gondoskodik a megrendel&eacute;s elutas&iacute;t&aacute;s&aacute;r&oacute;l sz&oacute;l&oacute; t&aacute;j&eacute;koztat&aacute;sr&oacute;l. A Szolg&aacute;ltat&oacute; nem z&aacute;rja ki a jelen Előfizetői Szerződ&eacute;s egyező akarattal t&ouml;rt&eacute;nő esetleges m&oacute;dos&iacute;t&aacute;s&aacute;nak lehetős&eacute;g&eacute;t. Az Előfizetői Szerződ&eacute;s m&oacute;dos&iacute;t&aacute;s&aacute;nak tov&aacute;bbi eseteit az &Aacute;SZF 9. fejezete tartalmazza. A Szolg&aacute;ltat&aacute;s sz&aacute;ml&aacute;z&aacute;sa &eacute;s a hat&aacute;rozott időtartam&uacute; elk&ouml;telezetts&eacute;g kezdete a Szolg&aacute;ltat&aacute;s aktiv&aacute;l&aacute;s&aacute;t&oacute;l kezdődik.</span></p>
<p><span style="font-size: 10pt;">&nbsp;</span></p>
<ol start="5">
<li><span style="font-size: 10pt;"><strong> A Szerződ&eacute;s időtartama</strong></span></li>
</ol>
<p><span style="font-size: 10pt;">Jelen Szerződ&eacute;st a Felek hat&aacute;rozatlan időtartamra k&ouml;tik meg egym&aacute;ssal, mely 30 (harminc) napos felmond&aacute;si idővel, indokol&aacute;s n&eacute;lk&uuml;l felmondhat&oacute; az Előfizető r&eacute;sz&eacute;ről.</span></p>
<ol start="6">
<li><span style="font-size: 10pt;"><strong> D&iacute;jak</strong></span></li>
</ol>
<p><span style="font-size: 10pt;">Az Előfizető a Szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tel&eacute;&eacute;rt</span></p>
<ol>
<li><span style="font-size: 10pt;">a) egyszeri d&iacute;jat;</span></li>
<li><span style="font-size: 10pt;">b) havid&iacute;jat;</span></li>
<li><span style="font-size: 10pt;">c) havi forgalmi d&iacute;jat k&ouml;teles fizetni.</span></li>
</ol>
<p><span style="font-size: 10pt;">Az egyszeri d&iacute;j a ki&eacute;p&iacute;t&eacute;st első havid&iacute;jjal egy&uuml;tt, a havid&iacute;j havonta előre, a havi forgalom havonta ut&oacute;lag fizetendő az adatlapon meghat&aacute;rozott d&iacute;jszab&aacute;s szerint, a Szolg&aacute;ltat&oacute; &aacute;ltal kibocs&aacute;tott sz&aacute;mla ellen&eacute;ben. Az Előfizető sz&aacute;ml&aacute;j&aacute;t eseti utal&aacute;ssal (a befizet&eacute;s azonos&iacute;t&aacute;s&aacute;ra szolg&aacute;l&oacute; k&ouml;zlem&eacute;ny rovatban a sz&aacute;mla sorsz&aacute;m&aacute;t fel kell t&uuml;ntetni), k&ouml;teles kiegyenl&iacute;teni a sz&aacute;mla ki&aacute;ll&iacute;t&aacute;s&aacute;t&oacute;l sz&aacute;m&iacute;tott 15 (tizen&ouml;t) napon bel&uuml;l.</span></p>
<p><span style="font-size: 10pt;">Az Előfizető k&ouml;teles a szolg&aacute;ltat&aacute;si d&iacute;jakat a r&eacute;sz&eacute;re megk&uuml;ld&ouml;tt sz&aacute;mla alapj&aacute;n havonta, a sz&aacute;ml&aacute;n felt&uuml;ntetett 15 (tizen&ouml;t) napos fizet&eacute;si hat&aacute;ridővel, banki &aacute;tutal&aacute;ssal megfizetni. K&eacute;sedelmes fizet&eacute;s eset&eacute;n a Szolg&aacute;ltat&oacute; a mindenkori jegybanki alapkamat k&eacute;tszeres&eacute;nek megfelelő k&eacute;sedelmi kamatra, valamint a szerződ&eacute;s azonnali hat&aacute;ly&uacute; felmond&aacute;s&aacute;ra jogosult.</span></p>
<p><span style="font-size: 10pt;">A Szolg&aacute;ltat&oacute; a Szolg&aacute;ltat&aacute;s d&iacute;jait jogosult a d&iacute;jv&aacute;ltoz&aacute;s hat&aacute;lyba l&eacute;p&eacute;s&eacute;t megelőzően legal&aacute;bb 15 (tizen&ouml;t) egyoldal&uacute;an m&oacute;dos&iacute;tani.</span></p>
<p><span style="font-size: 10pt;">A Szolg&aacute;ltat&oacute; legk&eacute;sőbb minden h&oacute;nap 5. (&ouml;t&ouml;dik) napj&aacute;n elektronikus d&iacute;jbek&eacute;rőt &aacute;ll&iacute;t ki, melyet &ndash; amennyiben az Előfizető rendelkezik ilyen hozz&aacute;f&eacute;r&eacute;ssel &ndash; el&eacute;rhetőv&eacute; tesz az Előfizető MyMCN oldal&aacute;n &eacute;s/vagy megk&uuml;ldi az Előfizető sz&aacute;m&aacute;ra, az Előfizető &aacute;ltal megadott e-mail c&iacute;mre. A d&iacute;jbek&eacute;rő ki&aacute;ll&iacute;tottnak &eacute;s k&eacute;zbes&iacute;tettnek tekintendő minden h&oacute;nap 5. (&ouml;t&ouml;dik) napj&aacute;t k&ouml;vetően.</span></p>
<p><span style="font-size: 10pt;">A Szolg&aacute;ltat&oacute; az Előfizető valamennyi ig&eacute;nybevett Szolg&aacute;ltat&aacute;s&aacute;r&oacute;l jogosult egyetlen sz&aacute;ml&aacute;t ki&aacute;ll&iacute;tani.</span></p>
<p><span style="font-size: 10pt;">Az Előfizető rendelkezhet t&ouml;bb Egy&eacute;ni Sz&aacute;ml&aacute;val is, de egy Egy&eacute;ni Sz&aacute;mla csak előre fizetett, vagy ut&oacute;lag fizetett forgalmi d&iacute;jas szolg&aacute;ltat&aacute;sokat kezelhet.</span></p>
<p><span style="font-size: 10pt;">Az Előfizető k&ouml;teles a beazonos&iacute;t&aacute;shoz sz&uuml;ks&eacute;ges inform&aacute;ci&oacute;kkal gondoskodni a tartoz&aacute;sa kiegyenl&iacute;t&eacute;s&eacute;ről, ennek hi&aacute;ny&aacute;ban a Szolg&aacute;ltat&oacute; a legk&ouml;zelebbi tartoz&aacute;ssal &eacute;rintett időszakhoz rendeli a be&eacute;rkezett &ouml;sszeget.</span></p>
<p><span style="font-size: 10pt;">Amennyiben az Előfizető a tartoz&aacute;st csak r&eacute;szben fizeti meg, &uacute;gy a Szolg&aacute;ltat&oacute; jogosult a be&eacute;rkezett &ouml;sszeget ar&aacute;nyosan, vagy egyes Szolg&aacute;ltat&aacute;sokhoz rendelten j&oacute;v&aacute;&iacute;rni.</span></p>
<p><span style="font-size: 10pt;"><strong>&nbsp;</strong></span></p>
<ol start="7">
<li><span style="font-size: 10pt;"><strong> Hibaelh&aacute;r&iacute;t&aacute;s</strong></span></li>
</ol>
<p><span style="font-size: 10pt;">7.1 Hib&aacute;s teljes&iacute;t&eacute;s eset&eacute;n a Szolg&aacute;ltat&oacute; minden tőle elv&aacute;rhat&oacute;t megtesz annak &eacute;rdek&eacute;ben, hogy a hiba bejelent&eacute;s&eacute;től sz&aacute;m&iacute;tott 72 (hetvenk&eacute;t) &oacute;r&aacute;n bel&uuml;l a hibaforr&aacute;st kik&uuml;sz&ouml;b&ouml;lje, &eacute;s a hib&aacute;tlan teljes&iacute;t&eacute;st biztos&iacute;tsa.</span></p>
<p><span style="font-size: 10pt;">Az esetleges hib&aacute;t a 2. pont szerinti el&eacute;rhetős&eacute;gek egyik&eacute;n lehet a Szolg&aacute;ltat&oacute; r&eacute;sz&eacute;re bejelenteni. Ha a kivizsg&aacute;l&aacute;s vagy a kijav&iacute;t&aacute;s kiz&aacute;r&oacute;lag a helysz&iacute;nen, az Előfizető helyis&eacute;g&eacute;ben &eacute;s az Előfizető &aacute;ltal meghat&aacute;rozott időpontban lehets&eacute;ges, vagy ha a kijav&iacute;t&aacute;s a Szolg&aacute;ltat&oacute; &eacute;s az Előfizető &aacute;ltal meghat&aacute;rozott időpontban a Szolg&aacute;ltat&oacute; &eacute;rdekk&ouml;r&eacute;n k&iacute;v&uuml;l eső okok miatt nem volt lehets&eacute;ges, a fenti 72 (hetvenk&eacute;t) &oacute;r&aacute;s hat&aacute;ridő a kies&eacute;s időtartam&aacute;val meghosszabbodik. Amennyiben a Szolg&aacute;ltat&aacute;s a hiba bejelent&eacute;s&eacute;től sz&aacute;m&iacute;tott 72 (hetvenk&eacute;t) &oacute;r&aacute;t meghalad&oacute; időtartamban a Szolg&aacute;ltat&oacute;nak felr&oacute;hat&oacute; ok miatt nem vehető ig&eacute;nybe, a Szolg&aacute;ltat&oacute; k&ouml;teles a 73. (hetvenharmadik) &oacute;r&aacute;t&oacute;l a hiba elh&aacute;r&iacute;t&aacute;s&aacute;ig tart&oacute; időszakra k&ouml;tb&eacute;rt fizetni az &Aacute;SZF 6.3 pontja szerint</span></p>
<p><span style="font-size: 10pt;">7.2 Szolg&aacute;ltat&oacute;t csak a szerződ&eacute;s szerinti szolg&aacute;ltat&aacute;sok ny&uacute;jt&aacute;s&aacute;val &eacute;s saj&aacute;t h&aacute;l&oacute;zat&aacute;val &ouml;sszef&uuml;gg&eacute;sben felmer&uuml;lt hib&aacute;k&eacute;rt terheli felelőss&eacute;g. Nem terheli felelőss&eacute;g a Szolg&aacute;ltat&oacute;t:</span></p>
<p><span style="font-size: 10pt;">7.2.1 Előfizető műszaki berendez&eacute;s&eacute;nek hib&aacute;ja vagy alkalmatlans&aacute;ga (pl. helyi h&aacute;l&oacute;zati eszk&ouml;z&ouml;k hib&aacute;ja),</span></p>
<p><span style="font-size: 10pt;">7.2.2 a műszaki berendez&eacute;s vagy a Szolg&aacute;ltat&aacute;s helytelen vagy rendeltet&eacute;sellenes haszn&aacute;lata,</span></p>
<p><span style="font-size: 10pt;">7.2.3 Előfizető &aacute;ltal a hozz&aacute;f&eacute;r&eacute;sben okozott hiba (pl. k&aacute;belszakad&aacute;s);</span></p>
<p><span style="font-size: 10pt;">7.2.4 a Szerződ&eacute;sben foglalt k&ouml;telezetts&eacute;g&eacute;nek vagy jogszab&aacute;lyi elő&iacute;r&aacute;sok Előfizető &aacute;ltali megszeg&eacute;se,</span></p>
<p><span style="font-size: 10pt;">7.2.5 A Szolg&aacute;ltat&aacute;s megszak&iacute;t&aacute;sa vagy korl&aacute;toz&aacute;sa, m&aacute;s Szolg&aacute;ltat&oacute; &aacute;ltal ny&uacute;jtott hozz&aacute;f&eacute;r&eacute;s vagy kapcsol&oacute;d&aacute;s megszakad&aacute;sa miatt,</span></p>
<p><span style="font-size: 10pt;">7.2.6 t&aacute;pell&aacute;t&aacute;s hib&aacute;ja, vagy</span></p>
<p><span style="font-size: 10pt;">7.2.7 vis major miatt.</span></p>
<p><span style="font-size: 10pt;">Előfizető a Szolg&aacute;ltat&aacute;ssal kapcsolatos hib&aacute;kat a Szolg&aacute;ltat&oacute; 2. pontj&aacute;ban ismertetett hibabejelentő el&eacute;rhetős&eacute;geken jelentheti be. A hibabejelent&eacute;s &eacute;s sz&aacute;mlapanasz kezel&eacute;s szab&aacute;lyait &eacute;s a Szolg&aacute;ltat&aacute;s minős&eacute;g&eacute;vel kapcsolatos rendelkez&eacute;seket az &Aacute;SZF 15. illetve 16. fejezete tartalmazza, valamint az &Aacute;SZF 5. sz. mell&eacute;klete</span></p>
<ol start="8">
<li><span style="font-size: 10pt;"><strong> A Szolg&aacute;ltat&aacute;s korl&aacute;toz&aacute;s&aacute;nak esetei</strong></span></li>
</ol>
<p><span style="font-size: 10pt;">A Szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tel&eacute;nek korl&aacute;toz&aacute;sa, &iacute;gy k&uuml;l&ouml;n&ouml;sen az Előfizető &aacute;ltal ind&iacute;tott vagy az Előfizetőn&eacute;l v&eacute;gződtetett forgalom korl&aacute;toz&aacute;s&aacute;ra, a Szolg&aacute;ltat&oacute; minős&eacute;gi vagy m&aacute;s jellemzőkkel cs&ouml;kkent&eacute;s&eacute;re a Szolg&aacute;ltat&oacute; - az Előfizető egyidejű &eacute;rtes&iacute;t&eacute;se mellett - a k&ouml;vetkező esetekben jogosult:</span></p>
<ol>
<li><span style="font-size: 10pt;">a) Előfizető akad&aacute;lyozza, vagy vesz&eacute;lyezteti a Szolg&aacute;ltat&oacute; rendszer&eacute;nek rendeltet&eacute;sszerű műk&ouml;d&eacute;s&eacute;t, &iacute;gy k&uuml;l&ouml;n&ouml;sen, ha az Előfizető a rendszerhez megfelelős&eacute;g-tan&uacute;s&iacute;t&aacute;ssal nem rendelkező v&eacute;gberendez&eacute;st csatlakoztat</span></li>
<li><span style="font-size: 10pt;">b) Előfizetőnek lej&aacute;rt d&iacute;jtartoz&aacute;sa van.</span></li>
</ol>
<p><span style="font-size: 10pt;">A Szolg&aacute;ltat&oacute; a korl&aacute;toz&aacute;st halad&eacute;ktalanul megsz&uuml;nteti, ha az Előfizető a korl&aacute;toz&aacute;s ok&aacute;t megsz&uuml;nteti &eacute;s erről a Szolg&aacute;ltat&oacute; hitelt &eacute;rdemlő m&oacute;don tudom&aacute;st szerez. A Szolg&aacute;ltat&aacute;s korl&aacute;toz&aacute;sa eset&eacute;n is a Szolg&aacute;ltat&oacute; biztos&iacute;tja:</span></p>
<ol>
<li><span style="font-size: 10pt;">a) az Előfizető h&iacute;vhat&oacute;s&aacute;g&aacute;t,</span></li>
<li><span style="font-size: 10pt;">b) a seg&eacute;lyk&eacute;rő h&iacute;v&aacute;sok tov&aacute;bb&iacute;t&aacute;s&aacute;t,</span></li>
<li><span style="font-size: 10pt;">c) a Szolg&aacute;ltat&oacute; &uuml;gyf&eacute;lszolg&aacute;lat&aacute;nak (hibabejelentőj&eacute;nek) el&eacute;rhetős&eacute;g&eacute;t.</span></li>
</ol>
<p><span style="font-size: 10pt;">Az Előfizetői szerződ&eacute;s megszűn&eacute;s&eacute;nek eseteit az &Aacute;SZF 12. pontja tartalmazza. Az Előfizetői szerződ&eacute;s sz&uuml;neteltet&eacute;s&eacute;ről az &Aacute;SZF 7. pontja, a Szolg&aacute;ltat&aacute;s korl&aacute;toz&aacute;s&aacute;nak felt&eacute;teleiről az &Aacute;SZF 9. pontja rendelkezik. Panasz eset&eacute;n az Előfizető az &Aacute;SZF 1. pontban felsorolt hat&oacute;s&aacute;gokhoz fordulhat.</span></p>
<p><span style="font-size: 10pt;">&nbsp;</span></p>
<ol start="9">
<li><span style="font-size: 10pt;"><strong> A Szerződ&eacute;s felmond&aacute;sa</strong></span></li>
</ol>
<p><span style="font-size: 10pt;">A szerződ&eacute;s mindk&eacute;t f&eacute;l al&aacute;&iacute;r&aacute;s&aacute;val &eacute;s/vagy egyező akaratnyilv&aacute;n&iacute;t&aacute;s&aacute;val j&ouml;n l&eacute;tre, &eacute;s a szolg&aacute;ltat&aacute;s biztos&iacute;t&aacute;s&aacute;val/ny&uacute;jt&aacute;s&aacute;val l&eacute;p hat&aacute;lyba.</span></p>
<p><span style="font-size: 10pt;">K&eacute;sedelmes fizet&eacute;s eset&eacute;n a Szolg&aacute;ltat&oacute; a mindenkori jegybanki alapkamat k&eacute;tszeres&eacute;nek megfelelő k&eacute;sedelmi kamatra, valamint a szerződ&eacute;s azonnali hat&aacute;ly&uacute; felmond&aacute;s&aacute;ra jogosult.</span></p>
<h2><span style="font-size: 10pt;">9.1. A Szolg&aacute;ltat&oacute; r&eacute;sz&eacute;ről t&ouml;rt&eacute;nő rendk&iacute;v&uuml;li felmond&aacute;s</span></h2>
<h2><span style="font-size: 10pt;">A Szolg&aacute;ltat&oacute; jogosult rendk&iacute;v&uuml;li felmond&aacute;ssal, azonnali hat&aacute;llyal megsz&uuml;ntetni a jelen szerződ&eacute;st, amennyiben:</span></h2>
<ul>
<li><span style="font-size: 10pt;">az Előfizető a sz&aacute;ml&aacute;kat - a havi sz&aacute;ml&aacute;n felt&uuml;ntetett fizet&eacute;si hat&aacute;ridőn t&uacute;l - k&eacute;sedelmesen fizeti;</span></li>
<li><span style="font-size: 10pt;">az Előfizető a Szolg&aacute;ltat&oacute; vonatkoz&oacute; &Aacute;ltal&aacute;nos Szerződ&eacute;si Felt&eacute;teleiben foglaltakat megs&eacute;rti;</span></li>
<li><span style="font-size: 10pt;">az Előfizető ellen csőd-, felsz&aacute;mol&aacute;si, v&eacute;gelsz&aacute;mol&aacute;si elj&aacute;r&aacute;s indul, ezekben az esetekben a Szolg&aacute;ltat&oacute; rendk&iacute;v&uuml;li felmond&aacute;s&aacute;nak joga a csőd-, felsz&aacute;mol&aacute;si, v&eacute;gelsz&aacute;mol&aacute;si elj&aacute;r&aacute;s &nbsp;kihirdet&eacute;se napj&aacute;n ny&iacute;lik meg.</span></li>
</ul>
<h2><span style="font-size: 10pt;">&nbsp;</span></h2>
<h2><span style="font-size: 10pt;">Egy&eacute;b rendelkez&eacute;sek &eacute;s nyilatkozatok</span></h2>
<p><span style="font-size: 10pt;">Tekintettel arra, hogy a Szolg&aacute;ltat&oacute; az elektronikus szerződ&eacute;sk&ouml;t&eacute;st &eacute;s kapcsolattart&aacute;st r&eacute;szes&iacute;ti előnyben, &iacute;gy előfizetői sz&aacute;m&aacute;ra szem&eacute;lyes webes fel&uuml;letet biztos&iacute;t MyMCN n&eacute;ven, amennyiben az Előfizető ezt ig&eacute;nyli. A MyMCN haszn&aacute;lata kiz&aacute;r&oacute;lag az arra &eacute;rv&eacute;nyes Felhaszn&aacute;l&aacute;si Felt&eacute;telek elfogad&aacute;sa mellett lehets&eacute;ges.</span></p>
<p><span style="font-size: 10pt;">A műszaki egyeztet&eacute;sek sor&aacute;n r&ouml;gz&iacute;tett adatok helyess&eacute;g&eacute;&eacute;rt az Előfizető felel, amennyiben a k&eacute;sőbbiekben ezek egy r&eacute;sze vagy eg&eacute;sze nem bizonyul helyt&aacute;ll&oacute;nak, az ebből eredő t&ouml;bbletk&ouml;lts&eacute;gek az Előfizetőt terhelik.</span></p>
<p><span style="font-size: 10pt;">Az előfizetői szerződ&eacute;st a Felek egyező akarattal, a szerződ&eacute;sk&ouml;t&eacute;s alakis&aacute;g&aacute;nak megfelelő form&aacute;ban m&oacute;dos&iacute;thatj&aacute;k.</span></p>
<p><span style="font-size: 10pt;">Az ig&eacute;nybevett Szolg&aacute;ltat&aacute;s nem egyetemes szolg&aacute;ltat&aacute;s.</span></p>
<p><span style="font-size: 10pt;">A szolg&aacute;ltat&oacute;i szerződ&eacute;sszeg&eacute;s jogk&ouml;vetkezm&eacute;nyeit, &iacute;gy k&uuml;l&ouml;n&ouml;sen a Szolg&aacute;ltat&aacute;s minős&eacute;g&eacute;re, sz&uuml;neteltet&eacute;s&eacute;re vonatkoz&oacute; rendelkez&eacute;sek megszeg&eacute;se eset&eacute;n az Előfizetőt megillető jogokat, a d&iacute;jvisszat&eacute;r&iacute;t&eacute;s rendj&eacute;t, az Előfizetőt megillető k&ouml;tb&eacute;r m&eacute;rt&eacute;k&eacute;t az &Aacute;SZF 6.3 pontja tartalmazza.</span></p>
<p><span style="font-size: 10pt;">A Szolg&aacute;ltat&aacute;s sz&uuml;neteltet&eacute;s&eacute;nek &eacute;s korl&aacute;toz&aacute;s&aacute;nak felt&eacute;teleit az &Aacute;SZF 5., karbantart&aacute;sra vonatkoz&oacute; inform&aacute;ci&oacute;kat az &Aacute;SZF 5.1.1. pontja tartalmazza.</span></p>
<p><span style="font-size: 10pt;">Az Előfizető d&iacute;jcsomagot a k&ouml;vetkező h&oacute;nap első napj&aacute;t&oacute;l kezdődően v&aacute;lthat, amennyiben az ezir&aacute;ny&uacute; k&eacute;relme a d&iacute;jcsomag v&aacute;lt&aacute;s hat&aacute;lyba l&eacute;p&eacute;s&eacute;t legk&eacute;sőbb az azt megelőző utols&oacute;előtti munkanapon be&eacute;rkezik a Szolg&aacute;ltat&oacute;hoz.</span></p>
<p><span style="font-size: 10pt;">Az Előfizető kifejezetten hozz&aacute;j&aacute;rul az elektronikus h&iacute;rk&ouml;zl&eacute;sről sz&oacute;l&oacute; 2003. &eacute;vi C. t&ouml;rv&eacute;ny (a tov&aacute;bbiakban: &bdquo;Eht.&rdquo;) 157. &sect; (2) bekezd&eacute;s&eacute;ben nem neves&iacute;tett adatai kezel&eacute;s&eacute;hez, illetve az adatai c&eacute;lhoz k&ouml;t&ouml;tt, Eht.-ban meghat&aacute;rozott c&eacute;lokt&oacute;l elt&eacute;rő m&oacute;don t&ouml;rt&eacute;nő felhaszn&aacute;l&aacute;s&aacute;hoz.</span></p>
<p><span style="font-size: 10pt;">Az Előfizető az e-mail c&iacute;me megad&aacute;s&aacute;val hozz&aacute;j&aacute;rul ahhoz, hogy a Szolg&aacute;ltat&oacute; hivatalos &eacute;rtes&iacute;t&eacute;st r&eacute;sz&eacute;re az &aacute;ltala megadott e-mail c&iacute;mre k&uuml;ldj&ouml;n elektronikus lev&eacute;l form&aacute;j&aacute;ban.</span></p>
<p><span style="font-size: 10pt;">Az Előfizető jelen dokumentum elfogad&aacute;s&aacute;val kijelenti, hogy a kapcsolattart&aacute;sra megjel&ouml;lt email-c&iacute;mre &eacute;rkező elektronikus &eacute;rtes&iacute;t&eacute;st (elektronikus dokumentumban vagy az elektronikus lev&eacute;lben foglalt &eacute;rtes&iacute;t&eacute;s) elfogadja &eacute;s v&aacute;llalja a k&eacute;zbes&iacute;t&eacute;si igazol&aacute;s megk&uuml;ld&eacute;s&eacute;t.</span></p>
<p><span style="font-size: 10pt;">A Szolg&aacute;ltat&oacute; fenntartja mag&aacute;nak a jogot, hogy hűs&eacute;ges előfizetői sz&aacute;m&aacute;ra időszakosan kedvezm&eacute;nyt biztos&iacute;tson.</span></p>
<p><span style="font-size: 10pt;">Jelen előfizetői szerződ&eacute;s mell&eacute;klete (1. sz&aacute;m&uacute; mell&eacute;klet) a tartalmazza a Szolg&aacute;ltat&aacute;s d&iacute;jait azzal, hogy amely d&iacute;jak nem ker&uuml;ltek felt&uuml;ntet&eacute;sre abban, &uacute;gy azokra a Szolg&aacute;ltat&oacute; mindenkor hat&aacute;lyos &Aacute;SZF-j&eacute;ben foglaltak alkalmazand&oacute;k.</span></p>
<p><span style="font-size: 10pt;">&nbsp;</span></p>
<p><span style="font-size: 10pt;">Felek meg&aacute;llapodnak abban, hogy jelen Szerződ&eacute;s &eacute;s a vonatkoz&oacute; &Aacute;SZF elt&eacute;r&eacute;se eset&eacute;n a jelen Szerződ&eacute;sben foglalt szab&aacute;lyok &eacute;rv&eacute;nyesek.</span></p>
<p><span style="font-size: 10pt;">&nbsp;</span></p>
<p><span style="font-size: 10pt;">Az Előfizető jelen előfizetői szerződ&eacute;sben szereplő felt&eacute;teleket elolvasta, megismerte &eacute;s &eacute;rtelmezte, &eacute;s azt - mint akarat&aacute;val mindenben megegyezőt -, arra feljogos&iacute;tott k&eacute;pviselője &uacute;tj&aacute;n &iacute;rta al&aacute;.</span></p>
<p><span style="font-size: 10pt;">&nbsp;</span></p>
<table>
<tbody>
<tr>
<td width="108">
<p><span style="font-size: 10pt;">d&aacute;tum</span></p>
</td>
<td width="192">
<p><span style="font-size: 10pt;">&nbsp;{$contract_date}</span></p>
</td>
<td width="173">
<p><span style="font-size: 10pt;">d&aacute;tum</span></p>
</td>
<td width="169">
<p><span style="font-size: 10pt;">_____________________</span></p>
</td>
</tr>
<tr>
<td width="108">
<p><span style="font-size: 10pt;">Szolg&aacute;ltat&oacute;</span></p>
</td>
<td width="192">
<p><span style="font-size: 10pt;">_____________________</span></p>
<p><span style="font-size: 10pt;">{$organization_name}</span></p>
</td>
<td width="173">
<p><span style="font-size: 10pt;">Előfizető</span></p>
</td>
<td width="169">
<p><span style="font-size: 10pt;">{$fio}</span></p>
</td>
</tr>
<tr>
<td width="108">
<p><span style="font-size: 10pt;">&nbsp;</span></p>
</td>
<td width="192">
<p><span style="font-size: 10pt;">&nbsp;</span></p>
</td>
<td width="173">
<p><span style="font-size: 10pt;">előfizető (nyomtatott betűkkel)</span></p>
</td>
<td width="169">
<p><span style="font-size: 10pt;">_____________________</span></p>
</td>
</tr>
</tbody>
</table>','contract');

INSERT INTO `document_template` VALUES (41,'DC_telefonia',3,'<p style="text-align: center;"><span style="font-size: 8pt;"><strong>Дополнительное соглашение № {$contract_dop_no}<br /></strong></span></p>
<p style="text-align: center;"><span style="font-size: 8pt;"><strong>К договору №{$contract_no} от {$contract_date|mdate:\'"d" месяца Y\'} г.</strong></span></p>
<p><span style="font-size: 8pt;"><strong>&nbsp;</strong></span></p>
<table style="width: 100%;">
<tbody>
<tr>
<td>
<p><span style="font-size: 8pt;"><strong>г. Москва </strong></span></p>
</td>
<td>
<p style="text-align: right;"><span style="font-size: 8pt;"><strong>{$contract_dop_date|mdate:\'"d" месяца Y\'} г.</strong></span></p>
</td>
</tr>
</tbody>
</table>
<p style="text-align: justify;"><span style="font-size: 8pt;">{$name_full}, {if $old_legal_type == "org"}именуемое в дальнейшем АБОНЕНТ, в качестве исполнительного органа и уполномоченного лица выступает {$position} {$fio}, действующий(ая) на основании Устава{else}именуемый(ая) в дальнейшем АБОНЕНТ,{/if} с одной стороны, и {$organization_name}, именуемое в дальнейшем ОПЕРАТОР,&nbsp;в качестве исполнительного органа и уполномоченного лица выступает {$organization_director_post} {$organization_director}, действующий(ая) на основании Устава, с другой стороны, именуемые в дальнейшем Стороны, заключили Дополнительное соглашение (далее Соглашение) о нижеследующем:</span></p>
<ol>
<li><span style="font-size: 8pt;"><strong> Описание Услуги</strong></span></li>
</ol>
<p><span style="font-size: 8pt;">1.1.&nbsp; ОПЕРАТОР предоставляет АБОНЕНТУ доступ к сети местной телефонной связи, возможность доступа к услугам внутризоновой связи и к сети оператора(ов) связи, оказывающего(их) услуги междугородной и международной телефонной связи междугородной и международной телефонной связи, в соответствии с условиями Договора, настоящего Соглашения, со стандартами и техническими нормами, установленными уполномоченными государственными органами РФ и условиями лицензий ОПЕРАТОРА и Заказов на Услугу.</span></p>
<p><span style="font-size: 8pt;">1.2.&nbsp; ОПЕРАТОР, на основании обращения АБОНЕНТА, оказывает также иные услуги, технологически</span><br /><span style="font-size: 8pt;"> неразрывно связанные с услугами телефонной связи: услуги связи по передаче данных для целей передачи&nbsp; голосовой информации; услуги телематических служб; услуги передачи данных.</span></p>
<p><span style="font-size: 8pt;">1.3.&nbsp; ОПЕРАТОР при предоставлении услуг телефонного соединения обеспечивает предоставление АБОНЕНТУ: </span><br /><span style="font-size: 8pt;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - доступа к системе информационно-справочного обслуживания: информация о тарифах на услуги телефонной связи, о состоянии лицевого счета АБОНЕНТА, а также иные, предусмотренные законодательством РФ и Договором, информационно-справочные услуги;</span><br /><span style="font-size: 8pt;"> &nbsp;&nbsp;&nbsp;&nbsp; - возможности бесплатного круглосуточного вызова экстренных оперативных служб.</span></p>
<p><span style="font-size: 8pt;">1.4.&nbsp; На основании обращения АБОНЕНТА или заказа Услуг через Личный кабинет, ОПЕРАТОР выделяет в пользование АБОНЕНТА один или более номеров с поддержкой соответствующего им количества одновременных соединений, каждое из которых осуществляется на отдельном телефонном порту Оборудования. Конкретные телефонные номера и соответствующее им количество одновременных соединений (линий), закрепленные за АБОНЕНТОМ, указываются в Заказе.</span></p>
<p><span style="font-size: 8pt;">1.5.&nbsp; Предоставление возможности доступа к услугам внутризоновой, междугородной и международной телефонной связи АБОНЕНТУ осуществляется при согласии АБОНЕНТА на доступ к таким услугам и на предоставление сведений о нем другим операторам связи для оказания таких услуг. Для получения доступа к услугам междугородной и международной телефонной связи АБОНЕНТУ необходимо указать в Заказе на Услугу название выбранного АБОНЕНТОМ оператора услуг междугородной и международной телефонной связи и способ выбора (предварительный выбор, либо выбор при каждом вызове).</span></p>
<p><span style="font-size: 8pt;">1.6.&nbsp; В зависимости от способа выбора оператора, предоставляющего услуги междугородной и международной телефонной связи, для получения Услуги АБОНЕНТУ необходимо использовать следующий план набора номера:</span></p>
<p><span style="font-size: 8pt;">&nbsp; (а)&nbsp; При предварительном выборе: </span><br /><span style="font-size: 8pt;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - при осуществлении междугородних соединений: 8 - код города (или код сети) - номер вызываемого абонента; </span><br /><span style="font-size: 8pt;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - при осуществлении международных соединений 8 - 10 - код страны - код города (или код сети) - номер вызываемого абонента. </span><br /> <br /><span style="font-size: 8pt;"> (б)&nbsp;&nbsp; При выборе при каждом вызове:</span><br /><span style="font-size: 8pt;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - при осуществлении междугородных соединений и международных соединений АБОНЕНТ обязуется использовать план набора номера, установленный ОПЕРАТОРОМ.</span></p>
<p><span style="font-size: 8pt;">1.7.&nbsp; Перечень основных и дополнительных услуг ОПЕРАТОРА, а так же тарифы, действующие на момент подписания настоящего Соглашения, опубликованы на Интернет-сайте <a href="http://www.mcn.ru">www.mcn.ru</a>.</span></p>
<p><span style="font-size: 8pt;">1.8.&nbsp; Доступ к сети местной телефонной связи ОПЕРАТОРА предоставляется при наличии технической возможности.</span></p>
<p><span style="font-size: 8pt;">1.9.&nbsp; В рамках Соглашения и соответствующего Заказа, ОПЕРАТОР в целях оказания Услуг АБОНЕНТУ, осуществляет комплекс действий для предоставления АБОНЕНТУ доступа к сети местной телефонной связи ОПЕРАТОРА (далее - &laquo;Подключение к Услугам&raquo;).</span></p>
<p><span style="font-size: 8pt;">1.10.&nbsp; По требованию АБОНЕНТА абонентская линия может быть сформирована ОПЕРАТОРОМ на имеющихся в пользовании у АБОНЕНТА каналах доступа, организованных ОПЕРАТОРОМ, или каналах связи, организованных АБОНЕНТОМ самостоятельно. ОПЕРАТОР не несет ответственности за техническое состояние и работоспособность каналов связи, организованных АБОНЕНТОМ, в том числе за прерывание оказания Услуг, связанное с техническим состоянием таких каналов связи.</span></p>
<ol start="2">
<li><span style="font-size: 8pt;"><strong> Порядок предоставления услуги</strong></span></li>
</ol>
<p><span style="font-size: 8pt;">2.1. После подписания Соглашения АБОНЕНТ в соответствии с условиями Договора и п. 3.1 настоящего Соглашения осуществляет на основании счета ОПЕРАТОРА платеж. В случае если АБОНЕНТ в течение 15 (пятнадцати) календарных дней после получения соответствующего счета не перечислит платеж в полном объеме в соответствии с п. 3.1 настоящего Соглашения, обязательства ОПЕРАТОРА, вытекающие из настоящего Соглашения, не возникают и настоящее Соглашение прекращает свое действие.</span></p>
<p><span style="font-size: 8pt;">2.2. ОПЕРАТОР осуществляет подключение к Услуге не позднее, чем через 5 рабочих дней со дня оплаты платежа по п. 3.1 настоящего Соглашения, при условии предоставления ОПЕРАТОРУ беспрепятственного доступа в помещения АБОНЕНТА.</span></p>
<p><span style="font-size: 8pt;">2.3. В случае если АБОНЕНТ в течение 30 (тридцати) рабочих дней со дня оплаты платежа по п. 3.1 настоящего Соглашения, не предоставляет&nbsp; ОПЕРАТОРУ беспрепятственный доступ в это помещение, ОПЕРАТОР вправе отказаться от исполнения своих обязательств по настоящему Соглашению, и настоящее Соглашение прекращает свое действие. В данном случае платеж, уплаченный АБОНЕНТОМ в порядке, установленном п. 2.1. настоящего Соглашения, возврату не подлежит.</span></p>
<p><span style="font-size: 8pt;">2.4.&nbsp;Дата подключения Услуг указывается Оператором в бланке Заказа. Для подтверждения Абонентом Заказа, изменения состава&nbsp; Услуги и тарифов Оператор высылает Абоненту по электронной почте бланк Заказа, который Абонент должен подписать и вернуть Оператору. Если в течение 5 дней Абонент не предоставит Оператору мотивированный отказ, то Услуги и тарифы, указанные в Заказе, считаются принятыми Абонентом, а Заказ подписанным.</span></p>
<p><span style="font-size: 8pt;">2.5. ОПЕРАТОР возвращает АБОНЕНТУ осуществленный АБОНЕНТОМ платеж в течение 10 рабочих дней после получения от АБОНЕНТА соответствующего письменного заявления в следующих случаях (при этом настоящее Соглашение прекращает свое действие с момента возврата платежа):</span></p>
<p><span style="font-size: 8pt;">&nbsp;&nbsp;&nbsp;&nbsp; - в случае одностороннего расторжения АБОНЕНТОМ настоящего Соглашения&nbsp;до подключения к Услуге по п.2.2 настоящего Соглашения; </span><br /><span style="font-size: 8pt;"> &nbsp;&nbsp;&nbsp; - в случае невозможности устранения ОПЕРАТОРОМ причин, вызвавших письменный мотивированный отказ АБОНЕНТА от подписания Заказа.</span></p>
<ol start="3">
<li><span style="font-size: 8pt;"><strong> Стоимость услуг и порядок расчетов</strong></span></li>
</ol>
<p><span style="font-size: 8pt;">3.1. Оплата Услуг, предоставляемых АБОНЕНТУ, осуществляется в порядке и размере согласно Заказам, вышеуказанному Договору, настоящему Соглашению и тарифам, опубликованным на Интернет-сайте <a href="http://www.mcn.ru">www.mcn.ru</a>.</span></p>
<p><span style="font-size: 8pt;">3.2. Ежемесячные платежи, предусмотренные согласно Заказам к настоящему Соглашению и тарифам, опубликованным на Интернет-сайте <a href="http://www.mcn.ru">www.mcn.ru</a>, начинают взиматься с даты подключения Услуги.&nbsp;</span></p>
<ol start="4">
<li><span style="font-size: 8pt;"><strong> Дополнительные условия</strong></span></li>
</ol>
<p><span style="font-size: 8pt;">4.1. АБОНЕНТ обязан соблюдать нормативные требования по нагрузке (трафику) на линии связи:</span></p>
<p><span style="font-size: 8pt;">&nbsp;- нагрузка на один порт не должна превышать 0,8 Эрланга;</span></p>
<p><span style="font-size: 8pt;">-&nbsp; нагрузка на один порт при безлимитном тарифном плане не должна превышать 5000 минут в месяц.</span></p>
<p><span style="font-size: 8pt;">В случае невыполнения этих условий ОПЕРАТОР имеет право по своему выбору ограничить предоставление Услуг или&nbsp; пересмотреть их стоимость.</span></p>
<p><span style="font-size: 8pt;">4.2. ОПЕРАТОР имеет право на полное или частичное прерывание предоставления Услуг, связанное с заменой оборудования, программного обеспечения или проведения других плановых работ, вызванных необходимостью поддержания работоспособности и развития сети, на общий срок не более чем 4 часа в течение месяца, оповестив АБОНЕНТА не менее чем за сутки до данного перерыва.</span></p>
<p><span style="font-size: 8pt;">4.3. Управление и настройка Оборудования АБОНЕНТА на время действия настоящего Соглашения осуществляется ОПЕРАТОРОМ. При несоблюдении данного условия линия разграничения ответственности между ОПЕРАТОРОМ и АБОНЕНТОМ устанавливается на порту оборудования ОПЕРАТОРА.</span></p>
<p><span style="font-size: 8pt;">4.4. Настоящее Соглашение вступает в силу с даты его подписания обеими Сторонами.</span></p>
<p><span style="font-size: 8pt;">4.5. Настоящее Соглашение прекращает свое действие в следующих случаях:</span></p>
<p><span style="font-size: 8pt;">&nbsp;&nbsp;&nbsp; - по инициативе АБОНЕНТА, с письменным уведомлением ОПЕРАТОРА не позднее, чем за 30 (тридцать) календарных дней до даты прекращения; </span><br /><span style="font-size: 8pt;"> &nbsp;&nbsp;&nbsp; - по инициативе ОПЕРАТОРА в соответствии с условиями пп. 2.1, 2.3, 2.5 и 4.1 настоящего договора, с предварительным уведомлением АБОНЕНТА.</span></p>
<p><span style="font-size: 8pt;">4.6. В случае нарушения АБОНЕНТОМ сроков оплаты Услуг, ОПЕРАТОР связи имеет право приостановить оказание Услуг до устранения нарушения, уведомив об этом АБОНЕНТА в письменной форме и с использованием средств связи оператора связи (автоинформатора). В случае неустранения такого нарушения в течение 6 (шести) месяцев с даты получения АБОНЕНТОМ от ОПЕРАТОРА связи уведомления (в письменной форме) о намерении приостановить оказание Услуг ОПЕРАТОР в одностороннем порядке вправе расторгнуть настоящее Соглашение.</span></p>
<table style="width: 100%;">
<tbody>
<tr>
<td>
<p><span style="font-size: 8pt;">ОПЕРАТОР</span><br /><span style="font-size: 8pt;"> __________________________</span><br /><span style="font-size: 8pt;"> {$organization_director_post} {$organization_director}</span></p>
</td>
<td>
<p><span style="font-size: 8pt;">АБОНЕНТ</span><br /><span style="font-size: 8pt;"> ________________________ </span><br /><span style="font-size: 8pt;"> {$position} {$fio}</span></p>
</td>
</tr>
</tbody>
</table>
<p><span style="font-size: 8pt;">&nbsp;</span></p>
<p><span style="font-size: 8pt;"><strong>&nbsp;</strong></span></p>', 'agreement');