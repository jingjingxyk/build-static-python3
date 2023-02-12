<?php
/**
 * @var $this SwooleCli\Preprocessor
 */
?>


export PATH=<?= implode(':', $this->binPaths) . PHP_EOL ?>
export ORIGIN_PATH=$PATH

SRC=<?= $this->phpSrcDir . PHP_EOL ?>
ROOT=$(pwd)
export CC=clang
export CXX=clang++
export LD=ld.lld
export PKG_CONFIG_PATH=<?= implode(':', $this->pkgConfigPaths) . PHP_EOL ?>
export ORIGIN_PKG_CONFIG_PATH=$PKG_CONFIG_PATH

OPTIONS="--disable-all \
<?php foreach ($this->extensionList as $item) : ?>
<?=$item->options?> \
<?php endforeach; ?>
<?=$this->extraOptions?>
"

<?php foreach ($this->libraryList as $item) : ?>
make_<?=$item->name?>() {
    <?php if ($item->skipBuildInstall == true): ?>
        echo "skip install library <?=$item->name?>" ;
        return 0 ;
    <?php endif ;?>

    cd <?=$this->workDir?>/thirdparty
    echo "build <?=$item->name?>"

    <?php if ($item->cleanBuildDirectory) : ?>
        test -d <?= $this->workDir ?>/thirdparty/<?= $item->name ?> && rm -rf <?= $this->workDir ?>/thirdparty/<?= $item->name ?><?= PHP_EOL; ?>
    <?php endif; ?>

    mkdir -p <?=$this->workDir?>/thirdparty/<?=$item->name .PHP_EOL ?>

    <?php if($item->untarArchiveCommand == 'tar' ):?>
        tar --strip-components=1 -C <?=$this->workDir?>/thirdparty/<?=$item->name?> -xf <?=$this->workDir?>/pool/lib/<?=$item->file?><?= PHP_EOL; ?>
    <?php endif ;?>
    <?php if($item->untarArchiveCommand == 'unzip'):?>
        unzip -d  <?=$this->workDir?>/thirdparty/<?=$item->name?>   <?=$this->workDir?>/pool/lib/<?=$item->file?> <?= PHP_EOL; ?>
    <?php endif ; ?>

    <?php if($item->untarArchiveCommand == 'mv'):?>
        cp -rf  <?=$this->workDir?>/pool/lib/<?=$item->file?> <?=$this->workDir?>/thirdparty/<?=$item->name?>/<?=$item->name?>    <?= PHP_EOL; ?>
    <?php endif ; ?>

    cd <?=$item->name?> ;

    <?php if (!empty($item->beforeConfigureScript)) : ?>
        <?= $item->beforeConfigureScript . PHP_EOL ?>
        result_code=$?
        [[ $result_code -gt 1 ]] &&  echo "[before configure script failure]" && exit $result_code;
    <?php endif; ?>

    cat <<'__EOF__'
    <?= $item->configure . PHP_EOL ?>
__EOF__

    <?php if (!empty($item->configure)): ?>
        <?=$item->configure . PHP_EOL ?>
        result_code=$?
        [[ $result_code -ne 0 ]] &&  echo "[configure failure]" && exit $result_code;
    <?php endif; ?>

    make -j <?=$this->maxJob?>  <?=$item->makeOptions . PHP_EOL ?>
    result_code=$?
    [[ $result_code -ne 0 ]] && echo "[make failure]" && exit $result_code;

    <?php if (!empty($item->beforeInstallScript)): ?>
        <?=$item->beforeInstallScript . PHP_EOL ?>
        result_code=$?
        [[ $result_code -ne 0 ]] &&  echo "[before install script  failure]" && exit $result_code;
    <?php endif; ?>

    make  <?= empty($item->makeInstallOptions)? "install" : $item->makeInstallOptions . PHP_EOL ?> <?= PHP_EOL ?>
    result_code=$?
    [[ $result_code -ne 0 ]] &&  echo "[make install failure]" && exit $result_code;

    <?php if ($item->afterInstallScript): ?>
        <?=$item->afterInstallScript . PHP_EOL ?>
        result_code=$?
        [[ $result_code -gt 1 ]] &&  echo "[after install script  failure]" && exit $result_code;
    <?php endif; ?>
    cd -
}

clean_<?=$item->name?>() {
    cd <?=$this->workDir?>/thirdparty
    echo "clean <?=$item->name?>"
    cd <?=$this->workDir?>/thirdparty/<?=$item->name?> && make clean
    cd -
}
<?php echo str_repeat(PHP_EOL, 1);?>
<?php endforeach; ?>

make_all_library() {
<?php foreach ($this->libraryList as $item) : ?>
    make_<?= $item->name ?> && [[ $? -eq 0 ]] && echo "[SUCCESS] make <?= $item->name ?>"
<?php endforeach; ?>
}

config_php() {
    export   ONIG_CFLAGS=$(pkg-config --cflags  --static oniguruma) ;
    export   ONIG_LIBS=$(pkg-config --libs  --static oniguruma) ;

    export   LIBSODIUM_CFLAGS=$(pkg-config --cflags  --static libsodium) ;
    export   LIBSODIUM_LIBS=$(pkg-config --libs  --static libsodium) ;

    export   LIBZIP_CFLAGS=$(pkg-config --cflags --static libzip) ;
    export   LIBZIP_LIBS=$(pkg-config --libs --static libzip) ;

    export   LIBPQ_CFLAGS=$(pkg-config  --cflags --static      libpq)
    export   LIBPQ_LIBS=$(pkg-config  --libs  --static       libpq)

    export   XSL_CFLAGS=$(pkg-config --cflags  --static libxslt) ;
    export   XSL_LIBS=$(pkg-config --libs  --static libxslt) ;



    export  ICU_CFLAGS=$(pkg-config --cflags icu-uc)  ;
    export  ICU_LIBS=$(pkg-config  --libs   icu-uc icu-io icu-i18n)  ;
:<<'EOF'
    export   NCURSES_CFLAGS=$(pkg-config --cflags --static  ncurses);
    export   NCURSES_LIBS=$(pkg-config  --libs --static ncurses);

    export   READLINE_CFLAGS=$(pkg-config --cflags --static readline)  ;
    export   READLINE_LIBS=$(pkg-config  --libs --static readline)  ;
EOF

    export  CPPFLAGS=$(pkg-config  --cflags --static  libpq libcares libffi icu-uc icu-io icu-i18n readline )
    LIBS=$(pkg-config  --libs --static   libpq libcares libffi icu-uc icu-io icu-i18n readline )
    export LIBS="$LIBS -L/usr/lib -lstdc++"

    test -f ./configure && rm ./configure ;
    ./buildconf --force
<?php if ($this->osType !== 'macos') : ?>
    mv main/php_config.h.in /tmp/cnt
    echo -ne '#ifndef __PHP_CONFIG_H\n#define __PHP_CONFIG_H\n' > main/php_config.h.in
    cat /tmp/cnt >> main/php_config.h.in
    echo -ne '\n#endif\n' >> main/php_config.h.in
<?php endif; ?>
    echo $OPTIONS
    echo $PKG_CONFIG_PATH
    ./configure $OPTIONS
}

make_php() {
    make EXTRA_CFLAGS='-fno-ident -Xcompiler -march=nehalem -Xcompiler -mtune=haswell -Os' \
    EXTRA_LDFLAGS_PROGRAM='-all-static -fno-ident <?=$this->extraLdflags?> <?php foreach ($this->libraryList as $item) {
        if (!empty($item->ldflags)) {
            echo $item->ldflags;
            echo ' ';
        }
    } ?>'  -j <?=$this->maxJob?> && echo ""
}

help() {
    echo "./make.sh docker-bash"
    echo "./make.sh config"
    echo "./make.sh build"
    echo "./make.sh archive"
    echo "./make.sh all-library"
    echo "./make.sh clean-all-library"
    echo "./make.sh sync"
}

if [ "$1" = "docker-build" ] ;then
  sudo docker build -t phpswoole/swoole_cli_os:<?= $this->dockerVersion ?> .
elif [ "$1" = "docker-bash" ] ;then
    sudo docker run -it -v $ROOT:<?=$this->workDir?> phpswoole/swoole_cli_os:<?= $this->dockerVersion ?> /bin/bash
    exit 0
elif [ "$1" = "all-library" ] ;then
    make_all_library
<?php foreach ($this->libraryList as $item) : ?>
elif [ "$1" = "<?=$item->name?>" ] ;then
    make_<?=$item->name?> && echo "[SUCCESS] make <?=$item->name?>"
elif [ "$1" = "clean-<?=$item->name?>" ] ;then
    clean_<?=$item->name?> && echo "[SUCCESS] make clean <?=$item->name?>"
<?php endforeach; ?>
elif [ "$1" = "config" ] ;then
    config_php
elif [ "$1" = "build" ] ;then
    make_php
elif [ "$1" = "archive" ] ;then
    cd bin
    SWOOLE_VERSION=$(./swoole-cli -r "echo SWOOLE_VERSION;")
    SWOOLE_CLI_FILE=swoole-cli-v${SWOOLE_VERSION}-<?=$this->getOsType()?>-x64.tar.xz
    strip swoole-cli
    tar -cJvf ${SWOOLE_CLI_FILE} swoole-cli LICENSE
    mv ${SWOOLE_CLI_FILE} ../
    cd -
elif [ "$1" = "clean-all-library" ] ;then
<?php foreach ($this->libraryList as $item) : ?>
    clean_<?=$item->name?> && echo "[SUCCESS] make clean [<?=$item->name?>]"
<?php endforeach; ?>
elif [ "$1" = "diff-configure" ] ;then
  meld $SRC/configure.ac ./configure.ac
elif [ "$1" = "pkg-check" ] ;then
<?php foreach ($this->libraryList as $item) : ?>
    echo "[<?= $item->name ?>]"
    pkg-config --libs <?= ($item->pkgName ?: $item->name) . PHP_EOL ?>
    echo "==========================================================="
<?php endforeach; ?>
elif [ "$1" = "sync" ] ;then
  echo "sync"
  # ZendVM
  cp -r $SRC/Zend ./
  # Extension
  cp -r $SRC/ext/bcmath/ ./ext
  cp -r $SRC/ext/bz2/ ./ext
  cp -r $SRC/ext/calendar/ ./ext
  cp -r $SRC/ext/ctype/ ./ext
  cp -r $SRC/ext/curl/ ./ext
  cp -r $SRC/ext/date/ ./ext
  cp -r $SRC/ext/dom/ ./ext
  cp -r $SRC/ext/exif/ ./ext
  cp -r $SRC/ext/fileinfo/ ./ext
  cp -r $SRC/ext/filter/ ./ext
  cp -r $SRC/ext/gd/ ./ext
  cp -r $SRC/ext/gettext/ ./ext
  cp -r $SRC/ext/gmp/ ./ext
  cp -r $SRC/ext/hash/ ./ext
  cp -r $SRC/ext/iconv/ ./ext
  cp -r $SRC/ext/intl/ ./ext
  cp -r $SRC/ext/json/ ./ext
  cp -r $SRC/ext/libxml/ ./ext
  cp -r $SRC/ext/mbstring/ ./ext
  cp -r $SRC/ext/mysqli/ ./ext
  cp -r $SRC/ext/mysqlnd/ ./ext
  cp -r $SRC/ext/opcache/ ./ext
  sed -i 's/ext_shared=yes/ext_shared=no/g' ext/opcache/config.m4 && sed -i 's/shared,,/$ext_shared,,/g' ext/opcache/config.m4
  echo -e '#include "php.h"\n\nextern zend_module_entry opcache_module_entry;\n#define phpext_opcache_ptr  &opcache_module_entry\n' > ext/opcache/php_opcache.h
  cp -r $SRC/ext/openssl/ ./ext
  cp -r $SRC/ext/pcntl/ ./ext
  cp -r $SRC/ext/pcre/ ./ext
  cp -r $SRC/ext/pdo/ ./ext
  cp -r $SRC/ext/pdo_mysql/ ./ext
  cp -r $SRC/ext/pdo_sqlite/ ./ext
  cp -r $SRC/ext/phar/ ./ext
  cp -r $SRC/ext/posix/ ./ext
  cp -r $SRC/ext/readline/ ./ext
  cp -r $SRC/ext/reflection/ ./ext
  cp -r $SRC/ext/session/ ./ext
  cp -r $SRC/ext/simplexml/ ./ext
  cp -r $SRC/ext/soap/ ./ext
  cp -r $SRC/ext/sockets/ ./ext
  cp -r $SRC/ext/sodium/ ./ext
  cp -r $SRC/ext/spl/ ./ext
  cp -r $SRC/ext/sqlite3/ ./ext
  cp -r $SRC/ext/standard/ ./ext
  cp -r $SRC/ext/sysvshm/ ./ext
  cp -r $SRC/ext/tokenizer/ ./ext
  cp -r $SRC/ext/xml/ ./ext
  cp -r $SRC/ext/xmlreader/ ./ext
  cp -r $SRC/ext/xmlwriter/ ./ext
  cp -r $SRC/ext/xsl/ ./ext
  cp -r $SRC/ext/zip/ ./ext
  cp -r $SRC/ext/zlib/ ./ext
  # main
  cp -r $SRC/main ./
  sed -i 's/\/\* start Zend extensions \*\//\/\* start Zend extensions \*\/\n\textern zend_extension zend_extension_entry;\n\tzend_register_extension(\&zend_extension_entry, NULL);/g' main/main.c
  # build
  cp -r $SRC/build ./
  # TSRM
  cp -r ./TSRM/TSRM.h main/TSRM.h
  cp -r $SRC/configure.ac ./
  # fpm
  cp -r $SRC/sapi/fpm/fpm ./sapi/cli
  exit 0
else
    help
fi

