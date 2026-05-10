@echo off
cd /d "%~dp0"
title WebSocket Server - DuLichPro

:loop
echo [%date% %time%] Starting WebSocket server on port 8080...
"C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe" scripts\websocket_server.php
echo [%date% %time%] Server stopped. Restarting in 3 seconds...
timeout /t 3 /nobreak >nul
goto loop
