#!/bin/sh

cd `dirname "$0"`

if [ -f .run ]; then 

    php ../core/Stop.php $@

else

    echo "\n";
    echo "\033[1;33m[WARN]\033[0m There is nothing to stop. CodeRunner Backendless debugging utility not running yet.";
    echo "\n";

fi;


