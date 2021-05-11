UPDATE app_report
SET excluded = CASE
                   WHEN app_report.phone_number LIKE '254%' THEN 1
                   WHEN app_report.phone_number LIKE '49%' THEN 1
                   WHEN app_report.full_names LIKE '%KREYE%' THEN 1
                   WHEN app_report.full_names LIKE '%C K%' THEN 1
                   ELSE 0
    END
WHERE id > 1;
