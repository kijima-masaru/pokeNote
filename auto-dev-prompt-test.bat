@echo off
setlocal

echo ==========================================
echo Sending message every 15 minutes.
echo Focus target input box before start.
echo Stop with Ctrl+C.
echo ==========================================
echo.
echo Starting in 5 seconds...
timeout /t 5 /nobreak >nul

:loop
powershell -NoProfile -ExecutionPolicy Bypass -Command "Add-Type -AssemblyName System.Windows.Forms; $ws = New-Object -ComObject WScript.Shell; $msg = [string]([char]0x958B+[char]0x767A+[char]0x3092+[char]0x9032+[char]0x3081+[char]0x3066+[char]0x304F+[char]0x3060+[char]0x3055+[char]0x3044+[char]0x3002); [System.Windows.Forms.Clipboard]::SetText($msg); Start-Sleep -Milliseconds 100; $ws.SendKeys('^v'); Start-Sleep -Milliseconds 150; $ws.SendKeys('{ENTER}')"
timeout /t 900 /nobreak >nul
goto loop
