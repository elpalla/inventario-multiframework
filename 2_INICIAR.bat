@echo off
chcp 65001 >nul
setlocal
cd /d "%~dp0"
set "PATH=C:\xampp\php;%PATH%"

if not exist "admin-core\vendor\autoload.php" ( echo Primero ejecuta 1_INSTALAR.bat & pause & exit /b 1 )
if not exist "api-servicios\vendor\autoload.php" ( echo Primero ejecuta 1_INSTALAR.bat & pause & exit /b 1 )

echo Iniciando los 3 servicios (se abriran 3 ventanas, no las cierres)...
start "Laravel Admin - http://localhost:8000" cmd /k "cd /d %~dp0admin-core && php artisan serve --port=8000"
start "API Slim - http://localhost:8081" cmd /k "cd /d %~dp0api-servicios && php -S localhost:8081 -t public"
start "Frontend - http://localhost:8082" cmd /k "cd /d %~dp0frontend && php -S localhost:8082"

timeout /t 4 >nul
start http://localhost:8082
start http://localhost:8000

echo.
echo  Frontend publico : http://localhost:8082
echo  Admin Laravel    : http://localhost:8000   (admin@voltrider.com / Admin12345)
echo  API Slim         : http://localhost:8081/api/productos
echo.
echo  Recuerda: MySQL de XAMPP debe estar encendido.
pause
