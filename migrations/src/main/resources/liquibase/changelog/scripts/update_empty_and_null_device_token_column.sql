update app_report
set device_token = 'NA'
where device_token = 'null'
   or device_token = ''
   or device_token is null;
