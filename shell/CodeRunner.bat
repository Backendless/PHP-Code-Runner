@echo off
title CodeRunner

:: tmp line for test
set PATH=%PATH%;c:\PHP

if NOT EXIST %CD%\.run (
    :: create file
	echo. 2>.run
	:: do hidden
	attrib +h %CD%\.run
) 

php ../core/Run.php %*


::echo Press any key.
pause > nul