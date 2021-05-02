CREATE TRIGGER exclusion_flag_insert_trigger
    BEFORE INSERT
    ON app_report
    FOR EACH ROW
BEGIN
    DECLARE exclude_me TINYINT;
    call exclusion_flag_evaluation(NEW.phone_number, NEW.full_names, @excludeFlag);

    select @excludeFlag into exclude_me;

    set NEW.excluded = exclude_me;
END;
