CREATE TRIGGER update_exclusion_list
    BEFORE UPDATE
    ON app_report
    FOR EACH ROW
BEGIN
    DECLARE exclude_me TINYINT;
    call update_exclusion_list(NEW.phone_number,NEW.full_names,@excludeFlag);

    select @excludeFlag into exclude_me;

    set NEW.excluded = exclude_me;
END;
