C:
cd C:\Program Files\MySQL\MySQL Server 5.7\bin\
echo off
set CUR_YYYY=%date:~10,4%
set CUR_MM=%date:~4,2%
set CUR_DD=%date:~7,2%
set CUR_HH=%time:~0,2%
if %CUR_HH% lss 10 (set CUR_HH=0%time:~1,1%)

set CUR_NN=%time:~3,2%
set CUR_SS=%time:~6,2%
set CUR_MS=%time:~9,2%

set SUBFILENAME=%CUR_YYYY%%CUR_MM%%CUR_DD%-%CUR_HH%%CUR_NN%%CUR_SS%
mysqldump -usonicradar -pLj@k15HGsd4df! sonicradar > \\u272172.your-storagebox.de\backup\SonicCV\backup\backup_%SUBFILENAME%.sql