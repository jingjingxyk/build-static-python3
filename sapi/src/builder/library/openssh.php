<?php

use SwooleCli\Library;
use SwooleCli\Preprocessor;

return function (Preprocessor $p) {
    $openssh_prefix = OPENSSH_PREFIX;
    $libedit_prefix = LIBEDIT_PREFIX;
    $zlib_prefix = ZLIB_PREFIX;
    $openssl_prefix = OPENSSL_PREFIX;

    $cflags = $p->getOsType() == 'macos' ? "" : '-static';
    $lib = new Library('openssh');
    $lib->withHomePage('https://www.openssh.com/')
        ->withLicense('https://anongit.mindrot.org/openssh.git/tree/LICENCE', Library::LICENSE_BSD)
        ->withManual('https://www.openssh.com/portable.html')
        ->withDocumentation('https://anongit.mindrot.org/openssh.git')
        ->withFile('openssh-V_9_9_P2.tar.gz')
        ->withHttpProxy(true, true)
        ->withDownloadScript(
            'openssh',
            <<<EOF
                git clone -b V_9_9_P2  https://anongit.mindrot.org/openssh.git
EOF
        )
        ->withPrefix($openssh_prefix)
        ->withConfigure(
            <<<EOF
            autoreconf -fi
            ./configure --help
            mkdir build
            cd build
            PACKAGES='zlib openssl  ncursesw' # libedit
            PACKAGES="\$PACKAGES  "

            CPPFLAGS="$(pkg-config  --cflags-only-I  --static \$PACKAGES)" \
            LDFLAGS="$(pkg-config   --libs-only-L    --static \$PACKAGES) {$cflags}" \
            LIBS="$(pkg-config      --libs-only-l    --static \$PACKAGES)" \

            ../configure \
            --prefix={$openssh_prefix} \
            --with-zlib={$zlib_prefix} \
            --with-ssl-dir={$openssl_prefix} \
            --with-pie \
            --with-cppflags="\$CPPFLAGS" \
            --with-ldflags="\$LDFLAGS" \
             --with-libs="\$LIBS"

             #--with-libedit={$libedit_prefix}  \

EOF
        )
        ->withBuildCached(false)
        ->withInstallCached(false)
        ->withDependentLibraries('openssl', 'zlib', 'libedit') # 'ncurses'
        ->disableDefaultLdflags()
        ->disablePkgName()
        ->disableDefaultPkgConfig()
        ->withSkipBuildLicense();

    $p->addLibrary($lib);
};
