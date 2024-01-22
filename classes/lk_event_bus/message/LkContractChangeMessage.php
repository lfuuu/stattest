<?php

namespace app\classes\lk_event_bus\message;


use app\classes\Utils;

/**
 * Формат сообщения
 *
    {
        "id": "1f3bb732-79ff-4268-967c-0c0a6b885355",
        "event_type": "contract_changed",
        "event_data": {
            "payload": {
                "id": "874987",
                "clientId": 173743,
                "businessId": 2,
                "contractId": 137317,
                "contragentId": 175887,
                "statResponse": {
                    "id": 137317,
                    "state": "unchecked",
                    "number": "137317",
                    "isPartner": false,
                    "businessId": 2,
                    "accountManager": "",
                    "businessProcessId": 21,
                    "canLoginAsClients": 0,
                    "partnerLoginAllow": 0,
                    "businessProcessStatusId": 201
                },
                "organizationId": 14,
                "businessProcessId": 21,
                "partnerLoginAllow": true,
                "supportLoginAllow": true,
                "accountManagerStat": "",
                "businessProcessStatusId": 201
            },
            "ipAddress": "10.252.14.124",
            "notificationDate": "2024-01-15T11:42:08.727Z",
            "event_ts": "2024-01-15T11:42:08.727Z"
        }
    }
 *
 */

class LkContractChangeMessage extends LkEventBusMessage
{
    public function getContractId(): int
    {
        return $this->getPayload()['contractId'] ?? 0;
    }

    public function getOrganizationId(): int
    {
        return $this->getPayload()['organizationId'] ?? 0;
    }
}