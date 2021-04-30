CREATE PROCEDURE process_rec_request(droid_request text, plumber_request text, created_at datetime, updated_at datetime)
BEGIN
    INSERT INTO app_report
    SET device_token = REPLACE(json_extract(droid_request, '$.userInfo.deviceToken'), '"', ''),
        country_code = REPLACE(json_extract(plumber_request, '$.country'), '"', ''),
        lat          =REPLACE(json_extract(plumber_request, '$.lat'), '"', ''),
        lon= REPLACE(json_extract(plumber_request, '$.lon'), '"', ''),
        full_names=REPLACE(json_extract(droid_request, '$.userInfo.userName'), '"', ''),
        phone_number=REPLACE(json_extract(droid_request, '$.userInfo.mobileNumber'), '"', ''),
        gender=REPLACE(json_extract(droid_request, '$.userInfo.gender'), '"', ''),
        user_type='NA',
        fr=IF(STRCMP(REPLACE(json_extract(plumber_request, '$.FR'), '"', ''), "false") = 1, 1, 0),
        ic=IF(STRCMP(REPLACE(json_extract(plumber_request, '$.IC'), '"', ''), "false") = 1, 1, 0),
        pp=IF(STRCMP(REPLACE(json_extract(plumber_request, '$.PP'), '"', ''), "false") = 1, 1, 0),
        sph=IF(STRCMP(REPLACE(json_extract(plumber_request, '$.SPH'), '"', ''), "false") = 1, 1, 0),
        spp=IF(STRCMP(REPLACE(json_extract(plumber_request, '$.SPP'), '"', ''), "false") = 1, 1, 0),
        created_at   = created_at,
        updated_at   = updated_at;
END
