CREATE OR REPLACE VIEW v_app_request_stats_view AS

SELECT app_report.id,
       app_report.created_at                    as request_date,
       app_report.device_token,
       app_report.country_code,
       app_report.lat,
       app_report.lon,
       upper(app_report.full_names)             as full_names,
       app_report.gender                        AS gender_name,
       app_report.excluded,
       CASE
           WHEN gender = 'Male' THEN 'M'
           WHEN gender = 'Mwanaume' THEN 'M'
           WHEN gender = 'Female' THEN 'F'
           WHEN gender = 'Mwanamke' THEN 'F'
           ELSE 'NA'
           END                                  AS gender,
       app_report.phone_number,
       IFNULL(user_feedback.user_type, 'OTHER') as user_type,
       CASE
           WHEN fr = 1 THEN 'FR'
           WHEN pp = 1 THEN 'PP'
           WHEN ic = 1 THEN 'IC'
           WHEN spp = 1 or sph = 1 THEN 'SPHS'
           ELSE 'NA'
           END
                                                AS use_case,
       app_report.created_at,
       app_report.updated_at

FROM app_report
         LEFT JOIN
     user_feedback ON app_report.device_token = user_feedback.device_token
