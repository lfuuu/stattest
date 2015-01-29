DROP PROCEDURE IF EXISTS `switch_bill_cleared`;

DELIMITER $$
CREATE DEFINER = `latyntsev`@`localhost` PROCEDURE `switch_bill_cleared`(in p_bill_no varbinary(32))
  begin
    declare p_sum_with_unapproved decimal(11,2) default 0;
    declare p_sum decimal(11,2) default 0;
    declare p_is_approved INTEGER default 0;
    declare p_client_id INTEGER(11) default 0;

    start transaction;
    select `client_id`, `is_approved`, `sum_with_unapproved`
    into p_client_id, p_is_approved, p_sum_with_unapproved
    from newbills
    where bill_no = p_bill_no lock in share mode;

    if p_is_approved > 0 then
      set p_is_approved = 0;
      set p_sum = 0;
    else
      set p_is_approved = 1;
      set p_sum = p_sum_with_unapproved;
    end if;

    update newbills
    set `sum` = p_sum, `is_approved` = p_is_approved
    where bill_no = p_bill_no;
    commit;

    call add_event('update_balance', p_client_id);

  end;$$
DELIMITER ;