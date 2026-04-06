@echo off
echo Menghapus database lama (jika ada)...
c:\xampp\mysql\bin\mysql.exe -u root -e "DROP DATABASE IF EXISTS db_booking_foto"
echo Mengimport database baru...
c:\xampp\mysql\bin\mysql.exe -u root < c:\xampp\htdocs\Jasa_Fotografi_Online\database.sql
echo Selesai! Database db_booking_foto berhasil diimport.
pause
