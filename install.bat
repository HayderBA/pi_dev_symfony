@echo off
cd /d "c:\Users\sarra\OneDrive\Bureau\pi_symf"
set PATH=C:\xampp\php;%PATH%
php composer.phar install
php bin/console doctrine:database:create --if-not-exists
php bin/console cache:clear
echo Installation complete!
pause
