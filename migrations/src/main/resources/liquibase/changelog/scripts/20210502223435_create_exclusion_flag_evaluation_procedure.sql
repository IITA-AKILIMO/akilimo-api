CREATE PROCEDURE exclusion_flag_evaluation(IN phone_number text, IN full_names text, out excluded TINYINT)
BEGIN

    IF (phone_number like '254%' OR phone_number like '49%') THEN
        SET excluded = 1;
    ELSEIF (full_names like '%KREYE%' OR full_names like '%C K%') THEN
        SET excluded = 1;
    ELSE
        SET excluded = 0;
    END IF;

END;
