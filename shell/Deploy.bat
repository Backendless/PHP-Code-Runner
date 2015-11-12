@echo off
title CodeRunner

:: tmp line for test
set PATH=%PATH%;c:\PHP

php ../core/Run.php %* deploy

echo Press any key

pause > nul