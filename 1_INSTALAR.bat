@echo off
chcp 65001 >nul
setlocal
title Instalacion - VoltRider Inventario
cd /d "%~dp0"

REM Usa el PHP de XAMPP aunque no este en el PATH de Windows
set "PATH=C:\xampp\php;%PATH%"

echo ============================================================
echo   INSTALACION - Sistema de Inventario Multi-Framework
echo ============================================================
echo.

where php >nul 2>nul
if errorlevel 1 (
  echo [ERROR] No se encontro PHP. Instala XAMPP en C:\xampp o agrega PHP al PATH.
  pause & exit /b 1
)
where composer >nul 2>nul
if errorlevel 1 (
  echo [ERROR] No se encontro Composer. Descargalo de https://getcomposer.org/download/
  echo         Instalalo, CIERRA esta ventana y vuelve a ejecutar este archivo.
  pause & exit /b 1
)

echo PHP detectado:
php -v | findstr /B "PHP"
php -r "exit(version_compare(PHP_VERSION,'8.2.0','<')?1:0);"
if errorlevel 1 (
  echo.
  echo [ERROR] Tu PHP es demasiado antiguo. Laravel 12 necesita PHP 8.2 o superior.
  echo         Instala una version reciente de XAMPP desde https://www.apachefriends.org
  echo         ^(la que trae PHP 8.2, 8.3 o 8.4^), ajusta la ruta C:\xampp\php y vuelve a ejecutar.
  pause
  exit /b 1
)
echo.


REM ---------- 1) Laravel (admin-core) ----------
if exist "admin-core\vendor\autoload.php" (
  echo [1/3] Laravel ya estaba instalado. Se omite la descarga.
) else (
  echo [1/3] Descargando Laravel 12 con Composer ^(puede tardar varios minutos^)...
  if exist "admin-core" rmdir /s /q "admin-core"
  call composer create-project laravel/laravel admin-core "12.*" --prefer-dist --no-interaction
  if errorlevel 1 ( echo [ERROR] Fallo la instalacion de Laravel. Revisa el mensaje de arriba ^(version de PHP o conexion a internet^). & pause & exit /b 1 )
)

echo.
echo [2/3] Copiando los archivos del proyecto dentro de Laravel...
REM Quitar migraciones por defecto de Laravel (usan la tabla "users")
del /q "admin-core\database\migrations\*.php" >nul 2>nul
xcopy "admin-core-archivos\*" "admin-core\" /E /Y /I /Q >nul
if errorlevel 1 ( echo [ERROR] No se pudieron copiar los archivos. & pause & exit /b 1 )
pushd admin-core
call php artisan key:generate --force
call php artisan config:clear >nul
call php artisan view:clear >nul
popd

echo.
echo [3/3] Instalando dependencias de la API Slim...
pushd api-servicios
call composer install --no-interaction
if errorlevel 1 ( echo [ERROR] Fallo composer install en api-servicios. & popd & pause & exit /b 1 )
popd

echo.
echo ============================================================
echo   INSTALACION COMPLETA
echo ============================================================
echo  Siguientes pasos:
echo   1. Abre XAMPP Control Panel e inicia Apache y MySQL.
echo   2. Entra a http://localhost/phpmyadmin  ^>  Importar
echo      y selecciona:  database\proyecto_multiframework.sql
echo   3. Ejecuta  2_INICIAR.bat
echo ============================================================
pause
