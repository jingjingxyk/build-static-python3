<?php

use SwooleCli\Library;
use SwooleCli\Preprocessor;

return function (Preprocessor $p) {
    $dav1d_prefix = DAV1D_PREFIX;
    $p->addLibrary(
        (new Library('dav1d'))
            ->withHomePage('https://code.videolan.org/videolan/dav1d/')
            ->withLicense('https://code.videolan.org/videolan/dav1d/-/blob/master/COPYING', Library::LICENSE_BSD)
            ->withUrl('https://code.videolan.org/videolan/dav1d/-/archive/1.1.0/dav1d-1.1.0.tar.gz')
            ->withFile('dav1d-1.1.0.tar.gz')
            ->withManual('https://code.videolan.org/videolan/dav1d')
            ->withPrefix($dav1d_prefix)
            ->withBuildLibraryCached(false)
            ->withCleanBuildDirectory()
            ->withCleanPreInstallDirectory($dav1d_prefix)
            ->withBuildScript(
                <<<EOF
                meson  -h
                meson setup -h
                # meson configure -h

                meson setup  build \
                -Dprefix={$dav1d_prefix} \
                -Dbackend=ninja \
                -Dbuildtype=release \
                -Ddefault_library=static \
                -Db_staticpic=true \
                -Db_pie=true \
                -Dprefer_static=true \
                -Denable_asm=true \
                -Denable_tools=true \
                -Denable_examples=true \
                -Denable_tests=false \
                -Denable_docs=false \
                -Dlogging=true \
                -Dfuzzing_engine=none

                meson compile -C build

                ninja -C build
                ninja -C build install


EOF
            )
            ->withPkgName('dav1d')
            ->withBinPath($dav1d_prefix . '/bin/')
            ->withPreInstallCommand(
                <<<EOF
# library dav1d :
apk add ninja python3 py3-pip  nasm
pip3 install meson
EOF
            )
    );
};
