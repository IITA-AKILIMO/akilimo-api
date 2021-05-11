update app_report
set gender = 'NA'
where gender = 'null'
   or gender = ''
   or gender is null;
