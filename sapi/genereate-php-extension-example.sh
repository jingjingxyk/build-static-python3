#!/usr/bin/env bash


__DIR__=$(
  cd "$(dirname "$0")"
  pwd
)
if [ -f ${__DIR__}/prepare.php ] ; then
  __PROJECT__=$(
    cd ${__DIR__}/
    pwd
  )
else
  __PROJECT__=$(
    cd ${__DIR__}/../
    pwd
  )
fi

cd ${__DIR__}


export PATH="${__PROJECT__}/bin/runtime:$PATH"

alias php='php -c ${__PROJECT__}/bin/runtime/php.ini'
php ${__PROJECT__}/sapi/DownloadPHPSourceCode.php


php ${__PROJECT__}/var/php-8.2.13/ext/ext_skel.php --help
# 创建扩展
php ${__PROJECT__}/var/php-8.2.13/ext/ext_skel.php  --ext example --author jingjingxyk --std

