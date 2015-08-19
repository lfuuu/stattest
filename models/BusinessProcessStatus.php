<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class BusinessProcessStatus extends ActiveRecord
{

    // �����
    const STATE_NEGOTIATIONS = 11; // ����������

    // �������
    const TELEKOM_MAINTENANCE_ORDER_OF_SERVICES = 19; // ����� �����
    const TELEKOM_MAINTENANCE_CONNECTED = 8; //������������
    const TELEKOM_MAINTENANCE_WORK = 9; // ����������
    const TELEKOM_MAINTENANCE_DISCONNECTED = 10; // �����������
    const TELEKOM_MAINTENANCE_DISCONNECTED_DEBT = 11; // ����������� �� �����
    const TELEKOM_MAINTENANCE_TRASH = 22; // �����
    const TELEKOM_MAINTENANCE_NOT_CONNECTED = 0; // �� �����������
    const TELEKOM_MAINTENANCE_TECH_FAILURE = 27; // ���. �����
    const TELEKOM_MAINTENANCE_FAILURE = 28; // �����
    const TELEKOM_MAINTENANCE_DUPLICATE = 29; // ��������

    const TELEKOM_SALES_INCOMING = 1; // ��������
    const TELEKOM_SALES_NEGOTIATIONS = 2; // � ������ �����������
    const TELEKOM_SALES_TESTING = 3; // �����������
    const TELEKOM_SALES_CONNECTING = 4; // ������������
    const TELEKOM_SALES_TECH_FAILURE = 5; // ��������
    const TELEKOM_SALES_FAILURE = 6; // �����
    const TELEKOM_SALES_TRASH = 7; // �����

    // �������� �������
    const INTERNET_SHOP_ACTING = 16; // �������������
    const INTERNET_SHOP_TRASH = 18; // �������������

    // ���������
    const PROVIDER_ORDERS_ACTING = 32; // ������ - ����������
    const PROVIDER_ORDERS_NEGOTIATION_STAGE = 36; // ������ - � ������ �����������
    const PROVIDER_MAINTENANCE_GPON = 108; // ������������� - GPON
    const PROVIDER_MAINTENANCE_VOLS = 109; // ������������� - ����
    const PROVIDER_MAINTENANCE_SERVICE = 110; // ������������� - ���������
    const PROVIDER_MAINTENANCE_ACTING = 15; // ������������� - ����������
    const PROVIDER_MAINTENANCE_CLOSED = 92; // ������������� - ��������
    const PROVIDER_MAINTENANCE_SELF_BUY = 93; // ������������� - �����������
    const PROVIDER_MAINTENANCE_ONCE = 94; // ������������� - �������

    // �������
    const PARTNER_MAINTENANCE_NEGOTIATIONS = 24; // ������������� - ����������
    const PARTNER_MAINTENANCE_ACTING = 35; // ������������� - �����������
    const PARTNER_MAINTENANCE_CLOSED = 26; // ������������� - ��������

    // ���������� ����
    const INTERNAL_OFFICE = 34; // ���������� ����
    const INTERNAL_OFFICE_CLOSED = 111; // ��������

    // ��������
    const OPERATOR_OPERATORS_INCOMING = 37; // ��������� - ��������
    const OPERATOR_OPERATORS_NEGOTIATIONS = 38; // ��������� - ����������
    const OPERATOR_OPERATORS_TESTING = 39; // ��������� - ������������
    const OPERATOR_OPERATORS_ACTING = 40; // ��������� - �����������
    const OPERATOR_OPERATORS_MANUAL_BILL = 107; // ��������� - ������ ����
    const OPERATOR_OPERATORS_SUSPENDED = 41; // ��������� - �������������
    const OPERATOR_OPERATORS_TERMINATED = 42; // ��������� - ����������
    const OPERATOR_OPERATORS_BLOCKED = 43; // ��������� - ���� ����������
    const OPERATOR_OPERATORS_TECH_FAILURE = 44; // ��������� - ��������
    const OPERATOR_OPERATORS_AUTO_BLOCKED = 45; // ��������� - ��������������
    const OPERATOR_OPERATORS_TRASH = 121; // ��������� - �����
    const OPERATOR_CLIENTS_INCOMING = 47; // ������� - ��������
    const OPERATOR_CLIENTS_NEGOTIATIONS = 48; // ������� - ����������
    const OPERATOR_CLIENTS_TESTING = 49; // ������� - ������������
    const OPERATOR_CLIENTS_ACTING = 50; // ������� - �����������
    const OPERATOR_CLIENTS_JIRASOFT = 56; // ������� - JiraSoft
    const OPERATOR_CLIENTS_SUSPENDED = 51; // ������� - �������������
    const OPERATOR_CLIENTS_TERMINATED = 52; // ������� - ����������
    const OPERATOR_CLIENTS_BLOCKED = 53; // ������� - ���� ����������
    const OPERATOR_CLIENTS_TECH_FAILURE = 54; // ������� - ��������
    const OPERATOR_CLIENTS_TRASH = 122; // ������� - �����
    const OPERATOR_INFRASTRUCTURE_INCOMING = 62; // �������������� - ��������
    const OPERATOR_INFRASTRUCTURE_NEGOTIATIONS = 63; // �������������� - ����������
    const OPERATOR_INFRASTRUCTURE_TESTING = 64; // �������������� - ������������
    const OPERATOR_INFRASTRUCTURE_ACTING = 65; // �������������� - �����������
    const OPERATOR_INFRASTRUCTURE_SUSPENDED = 66; // �������������� - �������������
    const OPERATOR_INFRASTRUCTURE_TERMINATED = 67; // �������������� - ����������
    const OPERATOR_INFRASTRUCTURE_BLOCKED = 68; // �������������� - ���� ����������
    const OPERATOR_INFRASTRUCTURE_TECH_FAILURE = 69; // �������������� - ��������
    const OPERATOR_INFRASTRUCTURE_TRASH = 123; // �������������� - �����
    const OPERATOR_FORMAL_INCOMING = 77; // ���������� - ��������
    const OPERATOR_FORMAL_NEGOTIATIONS = 78; // ���������� - ����������
    const OPERATOR_FORMAL_TESTING = 79; // ���������� - ������������
    const OPERATOR_FORMAL_ACTING = 80; // ���������� - �����������
    const OPERATOR_FORMAL_SUSPENDED = 81; // ���������� - �������������
    const OPERATOR_FORMAL_TERMINATED = 82; // ���������� - ����������
    const OPERATOR_FORMAL_BLOCKED = 83; // ���������� - ���� ����������
    const OPERATOR_FORMAL_TECH_FAILURE = 84; // ���������� - ��������
    const OPERATOR_FORMAL_TRASH = 124; // ���������� - �����

    const WELLTIME_MAINTENANCE_COMMISSIONING = 95; // �����-�������
    const WELLTIME_MAINTENANCE_MAINTENANCE = 96; // ���������������
    const WELLTIME_MAINTENANCE_MAINTENANCE_FREE = 97; // ��� ���������������
    const WELLTIME_MAINTENANCE_SUSPENDED = 98; // ����������������
    const WELLTIME_MAINTENANCE_FAILURE = 99; // �����
    const WELLTIME_MAINTENANCE_TRASH = 100; // �����


    const FOLDER_TELECOM_AUTOBLOCK = 21;

    public static function tableName()
    {
        return 'client_contract_business_process_status';
    }

    public static function getList()
    {
        $arr = self::find()->orderBy(['business_process_id' => SORT_ASC, 'sort' => SORT_ASC, 'id' => SORT_ASC])->all();;
        return ArrayHelper::map($arr, 'id', 'name');
    }

    public static function getTree()
    {
        $processes = [];
        foreach (BusinessProcess::find()->andWhere(['show_as_status' => '1'])->orderBy("sort")->all() as $b) {
            $processes[] = ["id" => $b->id, "up_id" => $b->contract_subdivision_id, "name" => $b->name];
        }

        $statuses = [];
        $bpStatuses = BusinessProcessStatus::find()->orderBy(['business_process_id' => SORT_ASC, 'sort' => SORT_ASC, 'id' => SORT_ASC])->all();
        foreach ($bpStatuses as $s) {
            $statuses[] = ["id" => $s['id'], "name" => $s['name'], "up_id" => $s['business_process_id']];
        }

        return ["processes" => $processes, "statuses" => $statuses];
    }

}
