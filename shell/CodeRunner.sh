#!/bin/sh

ROOT_PATH=$(cd $(dirname $0) && pwd);
cd $ROOT_PATH

if ! [ -f .run ]; then 
    touch .run
fi

php ../core/Run.php $@