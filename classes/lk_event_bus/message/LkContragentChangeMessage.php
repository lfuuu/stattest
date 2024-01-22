<?php

namespace app\classes\lk_event_bus\message;


use app\classes\Utils;

/**
 * Формат сообщения
 *
{
    "id": "e5b17468-66ff-4feb-93e6-2daa897d5fbe",
  "event_type": "contragent_changed",
  "event_data": {
    "payload": {
        "hid": "",
      "name": "Белозерова Анна Валерьевна",
      "esiaId": "1032654666",
      "isSync": false,
      "status": "verified",
      "orgType": "physical",
      "clientId": 173735,
      "langCode": "ru-RU",
      "contracts": [
        {
            "id": "874979",
          "clientId": 173735,
          "businessId": 2,
          "contractId": 137309,
          "contragentId": 175879,
          "statResponse": {
            "id": 137309,
            "state": "unchecked",
            "number": "137309",
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
        }
      ],
      "countryId": 643,
      "createdAt": "2024-01-13T16:51:13.563Z",
      "isLkFirst": true,
      "legalType": "person",
      "updatedAt": "2024-01-13T16:58:33.356Z",
      "userEditor": "",
      "bankResponse": {},
      "contragentId": 175879,
      "dataResponse": {
            "addr": {
                "PLV": {
            "id": 32257840,
            "city": "г Нижний Новгород",
            "eTag": "99D687946D3CD7AB7120C14443887EBC92766ECF",
            "flat": "4",
            "type": "PLV",
            "frame": "1",
            "house": "10",
            "region": "Нижегородская обл",
            "street": "ул Усиевича",
            "vrfDdt": "0,0,0",
            "zipCode": "603076",
            "district": "р-н Ленинский",
            "fiasCode": "d3430d8c-758b-45b1-bd62-88c68aca9349",
            "countryId": "RUS",
            "fiasCode2": "52-0-000-001-000-000-1159-0000-000",
            "addressStr": "Нижегородская обл, г Нижний Новгород, р-н Ленинский, ул Усиевича",
            "stateFacts": [
                        "Identifiable"
                    ],
            "fullAddressStr": "Нижегородская обл, г Нижний Новгород, р-н Ленинский, ул Усиевича, д. 10, корп. 1, кв. 4"
          },
          "PRG": {
            "id": 32257837,
            "city": "г Нижний Новгород",
            "eTag": "D84CF58422DAF6483458C20E10C6B5DD705E55F1",
            "flat": "4",
            "type": "PRG",
            "frame": "1",
            "house": "10",
            "region": "Нижегородская обл",
            "street": "ул Усиевича",
            "vrfDdt": "0,0,0",
            "zipCode": "603076",
            "district": "р-н Ленинский",
            "fiasCode": "d3430d8c-758b-45b1-bd62-88c68aca9349",
            "countryId": "RUS",
            "fiasCode2": "52-0-000-001-000-000-1159-0000-000",
            "addressStr": "Нижегородская обл, г Нижний Новгород, р-н Ленинский, ул Усиевича",
            "stateFacts": [
                        "Identifiable"
                    ],
            "fullAddressStr": "Нижегородская обл, г Нижний Новгород, р-н Ленинский, ул Усиевича, д. 10, корп. 1, кв. 4"
          }
        },
        "name": "Белозерова Анна Валерьевна",
        "email": "Belozerxxxxxxx@mail.ru",
        "phone": "7999xxxxx55",
        "gender": "F",
        "trusted": true,
        "birthday": "1979-09-02",
        "document": {
          "id": 38496642,
          "eTag": "728AB6C334425392116429xxxxxxxxxx65F9956B",
          "type": "RF_PASSPORT",
          "number": "19xxx2",
          "series": "2xx9",
          "vrfStu": "VERIFIED",
          "issueId": "5xxxx9",
          "issuedBy": "ОВД г.Кулебаки",
          "issueDate": "1999-12-03",
          "stateFacts": [
                    "EntityRoot"
                ]
        },
        "lastName": "Белозерова",
        "firstName": "Анна",
        "birthPlace": "город Кулебаки",
        "middleName": "Валерьевна",
        "citizenship": "RUS",
        "emailVerified": true,
        "phoneVerified": true
      },
      "statResponse": {
        "id": 175879,
        "name": "web_shop_ru_b2c ",
        "country": "RUS",
        "language": "ru-RU",
        "countryId": 643,
        "legalType": "person"
      },
      "statusUpdatedAt": "2024-01-13T13:58:33.575Z"
    },
    "ipAddress": "128.204.72.168",
    "notificationDate": "2024-01-13T13:58:33.599Z",
    "event_ts": "2024-01-13T13:58:33.599Z"
  }
}
*/

class LkContragentChangeMessage extends LkEventBusMessage
{
    public function getContragentId(): int
    {
        return $this->getPayload()['contragentId'] ?? 0;
    }
}