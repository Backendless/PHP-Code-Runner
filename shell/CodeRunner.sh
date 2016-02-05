#!/bin/sh

cd `dirname "$0"`

arg1=`echo $1 | grep -i driverHostPort`

if [ "$arg1" = "" ];then

    if ! [ -f .run ]; then 

        touch .run

    fi

fi

php ../core/Run.php $@
