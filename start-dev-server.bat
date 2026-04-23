@echo off
cd /d "%~dp0"
echo Starting GrowMind on http://localhost:8000
php -S localhost:8000 router.php
