<?php
class Sync1CServerHandler
{
    /**
     * @var Sync1CHelper
     */
    protected $helper;

    public function __construct($helper)
    {
        $this->helper = $helper;
    }

    public function statCreateClientCard($data)
    {
        $data = $data->clientInfo;

        $clientCard = new ClientCard();
        $clientCard->company = $data->Наименование;
        $clientCard->company_full = $data->Наименование;
        $clientCard->inn = $data->ИНН;
        $clientCard->type = $data->ЮрЛицо ? 'org' : 'priv';
        $clientCard->save();

        if ($data->ЭлектроннаяПочта) {
            $contactEmail = new ClientContact();
            $contactEmail->type = 'email';
            $contactEmail->client_id = $clientCard->id;
            $contactEmail->data = $data->ЭлектроннаяПочта;
            $contactEmail->is_active = 1;
            $contactEmail->is_official = 1;
            $contactEmail->save();
        }

        if ($data->ТелефонОрганизации) {
            $contactPhone = new ClientContact();
            $contactPhone->type = 'phone';
            $contactPhone->client_id = $clientCard->id;
            $contactPhone->data = $data->ТелефонОрганизации;
            $contactPhone->is_active = 1;
            $contactPhone->is_official = 1;
            $contactPhone->save();
        }

        $clientCardData = $this->helper->getClientCardData($clientCard->id);
        return array('return' => $clientCardData);
    }

    public function statSaveOrderShipper($data)
    {
        $data = $data->order;

        return array('return' => true);
    }
}