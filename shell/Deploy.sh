#!/bin/sh

ROOT_PATH=$(cd $(dirname $0) && pwd);
cd $ROOT_PATH

php ../core/Run.php $@ deploy
