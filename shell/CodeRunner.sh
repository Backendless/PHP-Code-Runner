#!/bin/sh

cd `dirname "$0"`

if ! [ -f .run ]; then 
    touch .run
fi

php ../core/Run.php $@