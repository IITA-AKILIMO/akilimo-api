INSERT INTO app_report (device_token, country_code, lat, lon, full_names, phone_number, gender, user_type, fr, ic, pp, sph, spp, created_at,
                        updated_at)
SELECT REPLACE(json_extract(droid_request, '$.userInfo.deviceToken'), '"', '')                 AS token,
       REPLACE(json_extract(plumber_request, '$.country'), '"', '')                            AS country_code,
       REPLACE(json_extract(plumber_request, '$.lat'), '"', '')                                AS lat,
       REPLACE(json_extract(plumber_request, '$.lon'), '"', '')                                AS lon,
       REPLACE(json_extract(droid_request, '$.userInfo.userName'), '"', '')                    AS full_names,
       REPLACE(json_extract(droid_request, '$.userInfo.mobileNumber'), '"', '')                AS phone_number,
       REPLACE(json_extract(droid_request, '$.userInfo.gender'), '"', '')                      AS gender,
       "NA"                                                                                    AS user_type,
       IF(STRCMP(REPLACE(json_extract(plumber_request, '$.FR'), '"', ''), "false") = 1, 1, 0)  as fr,
       IF(STRCMP(REPLACE(json_extract(plumber_request, '$.IC'), '"', ''), "false") = 1, 1, 0)  as ic,
       IF(STRCMP(REPLACE(json_extract(plumber_request, '$.PP'), '"', ''), "false") = 1, 1, 0)  as pp,
       IF(STRCMP(REPLACE(json_extract(plumber_request, '$.SPH'), '"', ''), "false") = 1, 1, 0) as sph,
       IF(STRCMP(REPLACE(json_extract(plumber_request, '$.SPP'), '"', ''), "false") = 1, 1, 0) as spp,
       created_at,
       updated_at
FROM request_response
ORDER BY created_at desc;
