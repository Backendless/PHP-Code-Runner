@echo off
title StopCodeRunner

:: tmp line for test
set PATH=%PATH%;c:\PHP

if EXIST %CD%\.run (

  php ../core/Stop.php %*
  
) else (

    echo. 
    echo [WARN] There is nothing to stop. CodeRunner Backendless debugging utility not running yet.
    echo. 

)

echo Press any key

pause > nul


