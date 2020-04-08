<?php

/**
 * Class m200408_113604_paymetn_terminal
 */
class m200408_113604_paymetn_terminal extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(\app\models\Payment::tableName(),  'type', "enum('bank','prov','ecash','neprov','creditnote','terminal') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'bank'");
        $this->execute('DROP FUNCTION IF EXISTS `get_payment_type_description`');

        $sql = <<<SQL
CREATE FUNCTION `get_payment_type_description`(type       VARCHAR(32), ecash_operator VARCHAR(32), bank VARCHAR(32),
                                               payment_no VARCHAR(32), payment_date VARCHAR(32), comment VARCHAR(256))
  RETURNS VARCHAR(1024)
  DETERMINISTIC
  READS SQL DATA
  BEGIN
    DECLARE _bank VARCHAR(64) DEFAULT '';
    DECLARE _ecashPaymentNo VARCHAR(64) DEFAULT '';
    DECLARE _str VARCHAR(128) DEFAULT '';
    DECLARE CONTINUE HANDLER FOR 1339 BEGIN END;

    CASE type
      WHEN 'bank'
      THEN
        IF bank = 'sber'
        THEN
          SET _bank := 'Сбербанк';
        ELSEIF bank = 'ural'
          THEN
            SET _bank := 'УралСиб';
        END IF;
        RETURN concat('Банковский платеж ', _bank, ' №', payment_no, ' от ', payment_date);

      WHEN 'prov'
      THEN RETURN 'Наличность';
      WHEN 'terminal'
      THEN RETURN 'Оплата картой через платежный терминал в офисе';
      WHEN 'ecash'
      THEN

        SET _ecashPaymentNo := trim(substring(comment, length(SUBSTRING_INDEX(comment, "#", 1)) + 2));
        SET _str = 'Электроный платеж: ';

        IF _ecashPaymentNo LIKE '%at%'
        THEN
          SET _ecashPaymentNo := trim(substring_index(_ecashPaymentNo, 'at', 1));
        ELSEIF _ecashPaymentNo LIKE '%(%'
          THEN
            SET _ecashPaymentNo := trim(substring_index(_ecashPaymentNo, '(', 1));
        END IF;

        CASE ecash_operator
          WHEN 'yandex'
          THEN SET _str := concat(_str, 'Яндекс.Деньги');
          WHEN 'sberbank'
          THEN SET _str := concat(_str, 'Сбербанк.Онлайн');
          WHEN 'qiwi'
          THEN SET _str := concat(_str, 'QIWI');
        END CASE;

        IF length(_ecashPaymentNo) > 0
        THEN
          SET _str := concat(_str, ' №', _ecashPaymentNo);
        END IF;

        RETURN _str;

    ELSE RETURN 'Наличность';
    END CASE;

    RETURN 'Банковский платеж';
    
    
  END;


SQL;

        $this->execute($sql);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(\app\models\Payment::tableName(),  'type', "enum('bank','prov','ecash','neprov','creditnote') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'bank'");
        $this->execute('DROP FUNCTION IF EXISTS `get_payment_type_description`');

        $sql = <<<SQL
CREATE FUNCTION `get_payment_type_description`(type       VARCHAR(32), ecash_operator VARCHAR(32), bank VARCHAR(32),
                                               payment_no VARCHAR(32), payment_date VARCHAR(32), comment VARCHAR(256))
  RETURNS VARCHAR(1024)
DETERMINISTIC
READS SQL DATA
  BEGIN
    DECLARE _bank VARCHAR(64) DEFAULT '';
    DECLARE _ecashPaymentNo VARCHAR(64) DEFAULT '';
    DECLARE _str VARCHAR(128) DEFAULT '';
    DECLARE CONTINUE HANDLER FOR 1339 BEGIN END;

    CASE type
      WHEN 'bank'
      THEN
        IF bank = 'sber'
        THEN
          SET _bank := 'Сбербанк';
        ELSEIF bank = 'ural'
          THEN
            SET _bank := 'УралСиб';
        END IF;
        RETURN concat('Банковский платеж ', _bank, ' №', payment_no, ' от ', payment_date);

      WHEN 'prov'
      THEN RETURN 'Наличность';
      WHEN 'ecash'
      THEN

        SET _ecashPaymentNo := trim(substring(comment, length(SUBSTRING_INDEX(comment, "#", 1)) + 2));
        SET _str = 'Электроный платеж: ';

        IF _ecashPaymentNo LIKE '%at%'
        THEN
          SET _ecashPaymentNo := trim(substring_index(_ecashPaymentNo, 'at', 1));
        ELSEIF _ecashPaymentNo LIKE '%(%'
          THEN
            SET _ecashPaymentNo := trim(substring_index(_ecashPaymentNo, '(', 1));
        END IF;

        CASE ecash_operator
          WHEN 'yandex'
          THEN SET _str := concat(_str, 'Яндекс.Деньги');
          WHEN 'sberbank'
          THEN SET _str := concat(_str, 'Сбербанк.Онлайн');
          WHEN 'qiwi'
          THEN SET _str := concat(_str, 'QIWI');
        END CASE;

        IF length(_ecashPaymentNo) > 0
        THEN
          SET _str := concat(_str, ' №', _ecashPaymentNo);
        END IF;

        RETURN _str;

    ELSE RETURN 'Наличность';
    END CASE;

    RETURN 'Банковский платеж';


  END;


SQL;

        $this->execute($sql);
    }
}
