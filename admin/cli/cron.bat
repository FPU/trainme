@echo off
for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
set "YY=%dt:~2,2%" & set "YYYY=%dt:~0,4%" & set "MM=%dt:~4,2%" & set "DD=%dt:~6,2%"
set "HH=%dt:~8,2%" & set "Min=%dt:~10,2%" & set "Sec=%dt:~12,2%"

set "fullstamp=%YYYY%-%MM%-%DD%_%HH%-%Min%-%Sec%"

REM "C:\Program Files\PHP\v7.0\php.exe" -f  "M:\Moodle Production\webroot33\admin\cli\cron.php" > "M:\Moodle Production\cron\%fullstamp%_standardcron.txt"

"C:\PHP72\php.exe" -f  "d:\moodle342\admin\cli\cron.php" > "d:\cron\%fullstamp%_standardcron.txt"


forfiles -p "d:\cron" -s -m *.* /D -8 /C "cmd /c del @path" 