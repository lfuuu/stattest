DROP TRIGGER `to_postgres_usage_voip_after_ins_tr`;

DROP TRIGGER `to_postgres_usage_voip_after_upd_tr`;

DROP TRIGGER `to_postgres_usage_voip_after_del_tr`;

DELIMITER ;;
CREATE DEFINER=`latyntsev`@`localhost` TRIGGER `to_postgres_usage_voip_after_ins_tr` AFTER INSERT ON `usage_voip`
FOR EACH ROW BEGIN
	call z_sync_postgres('usage_voip', NEW.id);

             call update_voip_number(NEW.E164, NEW.edit_user_id);
END;;

CREATE DEFINER=`latyntsev`@`localhost` TRIGGER `to_postgres_usage_voip_after_upd_tr` AFTER UPDATE ON `usage_voip`
FOR EACH ROW BEGIN
                call z_sync_postgres('usage_voip', NEW.id);
                call update_voip_number(NEW.E164, NEW.edit_user_id);
END;;

CREATE DEFINER=`latyntsev`@`localhost` TRIGGER `to_postgres_usage_voip_after_del_tr` AFTER DELETE ON `usage_voip`
FOR EACH ROW BEGIN
                call z_sync_postgres('usage_voip', OLD.id);
                call update_voip_number(OLD.E164, OLD.edit_user_id);
END;;
DELIMITER ;


DROP TRIGGER `to_postgres_log_tarif_after_ins_tr`;

DROP TRIGGER `to_postgres_log_tarif_after_upd_tr`;

DROP TRIGGER `to_postgres_log_tarif_after_del_tr`;

DELIMITER ;;
CREATE DEFINER=`latyntsev`@`localhost` TRIGGER `to_postgres_log_tarif_after_ins_tr` AFTER INSERT ON `log_tarif`
FOR EACH ROW BEGIN
     IF NEW.service = 'usage_voip' THEN
	     call z_sync_postgres('log_tarif', NEW.id);
     END IF;
END;;

CREATE DEFINER=`latyntsev`@`localhost` TRIGGER `to_postgres_log_tarif_after_upd_tr` AFTER UPDATE ON `log_tarif`
FOR EACH ROW BEGIN
     IF NEW.service = 'usage_voip' THEN
	     call z_sync_postgres('log_tarif', NEW.id);
     END IF;
END;;

CREATE DEFINER=`latyntsev`@`localhost` TRIGGER `to_postgres_log_tarif_after_del_tr` AFTER DELETE ON `log_tarif`
FOR EACH ROW BEGIN
     IF OLD.service = 'usage_voip' THEN
	     call z_sync_postgres('log_tarif', OLD.id);
     END IF;
END;;
DELIMITER ;


