<?php


use SwooleCli\Library;
use SwooleCli\Preprocessor;

function install_libjpeg(Preprocessor $p)
{
    $libjpeg_prefix = JPEG_PREFIX;
    $lib = new Library('libjpeg');
    $lib->withHomePage('https://libjpeg-turbo.org/')
        ->withLicense('https://github.com/libjpeg-turbo/libjpeg-turbo/blob/main/LICENSE.md', Library::LICENSE_BSD)
        ->withUrl('https://codeload.github.com/libjpeg-turbo/libjpeg-turbo/tar.gz/refs/tags/2.1.2')
        ->withFile('libjpeg-turbo-2.1.2.tar.gz')
        ->withPrefix($libjpeg_prefix)
        ->withCleanBuildDirectory()
        ->withCleanPreInstallDirectory($libjpeg_prefix)
        ->withConfigure('cmake -G"Unix Makefiles" -DENABLE_STATIC=1 -DENABLE_SHARED=0  -DCMAKE_INSTALL_PREFIX=' . $libjpeg_prefix . ' .')
        ->withPkgName('libjpeg');

    // linux 系统中是保存在 /usr/lib64 目录下的，而 macos 是放在 /usr/lib 目录中的，不清楚这里是什么原因？
    $jpeg_lib_dir = $libjpeg_prefix . '/' . ($p->getOsType() === 'macos' ? 'lib' : 'lib64');
    $lib->withLdflags('-L' . $jpeg_lib_dir)
        ->withPkgConfig($jpeg_lib_dir . '/pkgconfig');
    if ($p->getOsType() === 'macos') {
        $lib->withScriptAfterInstall('find ' . $lib->prefix . ' -name \*.dylib | xargs rm -f');
    }
    $p->addLibrary($lib);
}

function install_libgif(Preprocessor $p)
{
    $libgif_prefix = GIF_PREFIX;
    $p->addLibrary(
        (new Library('libgif'))
            ->withUrl('https://nchc.dl.sourceforge.net/project/giflib/giflib-5.2.1.tar.gz')
            ->withLicense('https://giflib.sourceforge.net/intro.html', Library::LICENSE_SPEC)
            ->withPrefix($libgif_prefix)
            ->withCleanBuildDirectory()
            ->withCleanBuildDirectory()
            ->withMakeOptions('libgif.a')
            ->withMakeInstallCommand('')
            ->withScriptAfterInstall(
                <<<EOF
                if [ ! -d {$libgif_prefix}/lib ]; then
                    mkdir -p {$libgif_prefix}/lib
                fi
                if [ ! -d {$libgif_prefix}/include ]; then
                    mkdir -p {$libgif_prefix}/include
                fi
                cp libgif.a {$libgif_prefix}/lib/libgif.a
                cp gif_lib.h {$libgif_prefix}/include/gif_lib.h
                EOF
            )
            ->withLdflags('-L' . $libgif_prefix . '/lib')
            ->withPkgName('')
            ->withPkgConfig('')
    );
    if (0) {
        $p->addLibrary(
            (new Library('giflib'))
                ->withUrl('https://nchc.dl.sourceforge.net/project/giflib/giflib-5.2.1.tar.gz')
                ->withLicense('http://giflib.sourceforge.net/intro.html', Library::LICENSE_SPEC)
                ->withCleanBuildDirectory()
                ->withPrefix('/usr/giflib')
                ->withScriptBeforeConfigure(
                    '

                default_prefix_dir="/ u s r" # 阻止 macos 系统下编译路径被替换
                # 替换空格
                default_prefix_dir=$(echo "$default_prefix_dir" | sed -e "s/[ ]//g")

                sed -i.bakup "s@PREFIX = $default_prefix_dir/local@PREFIX = /usr/giflib@" Makefile

                cat >> Makefile <<"EOF"
install-lib-static:
    $(INSTALL) -d "$(DESTDIR)$(LIBDIR)"
    $(INSTALL) -m 644 libgif.a "$(DESTDIR)$(LIBDIR)/libgif.a"
EOF


                '
                )
                ->withMakeOptions('libgif.a')
                //->withMakeOptions('all')
                ->withMakeInstallOptions('install-include && make  install-lib-static')
                # ->withMakeInstallCommand('install-include DESTDIR=/usr/giflib && make  install-lib-static DESTDIR=/usr/giflib')
                # ->withMakeInstallOptions('DESTDIR=/usr/libgif')
                ->withLdflags('-L/usr/giflib/lib')
                ->disableDefaultPkgConfig()
        );
    }
}

function install_libpng(Preprocessor $p)
{
    $libpng_prefix = PNG_PREFIX;
    $libzlib_prefix = ZLIB_PREFIX;
    $p->addLibrary(
        (new Library('libpng'))
            ->withUrl('https://nchc.dl.sourceforge.net/project/libpng/libpng16/1.6.37/libpng-1.6.37.tar.gz')
            ->withLicense('http://www.libpng.org/pub/png/src/libpng-LICENSE.txt', Library::LICENSE_SPEC)
            ->withPrefix($libpng_prefix)
            ->withCleanBuildDirectory()
            ->withCleanPreInstallDirectory($libpng_prefix)
            ->withConfigure(
                <<<EOF
                ./configure --help
                CPPFLAGS="$(pkg-config  --cflags-only-I  --static zlib )" \
                LDFLAGS="$(pkg-config --libs-only-L      --static zlib )" \
                LIBS="$(pkg-config --libs-only-l         --static zlib )" \
                ./configure --prefix={$libpng_prefix} \
                --enable-static --disable-shared \
                --with-zlib-prefix={$libzlib_prefix} \
                --with-binconfigs
EOF
            )
            ->withPkgName('libpng16')
            ->depends('zlib')
    );
}

function install_libwebp(Preprocessor $p)
{
    $libwebp_prefix = WEBP_PREFIX;
    $libpng_prefix = PNG_PREFIX;
    $libjpeg_prefix = JPEG_PREFIX;
    $libgif_prefix = GIF_PREFIX;
    $jpeg_lib_dir = $libjpeg_prefix . '/' . ($p->getOsType() === 'macos' ? 'lib' : 'lib64');
    $p->addLibrary(
        (new Library('libwebp'))
            ->withUrl('https://codeload.github.com/webmproject/libwebp/tar.gz/refs/tags/v1.2.1')
            ->withFile('libwebp-1.2.1.tar.gz')
            ->withHomePage('https://github.com/webmproject/libwebp')
            ->withLicense('https://github.com/webmproject/libwebp/blob/main/COPYING', Library::LICENSE_SPEC)
            ->withPrefix($libwebp_prefix)
            ->withCleanBuildDirectory()
            ->withCleanPreInstallDirectory($libwebp_prefix)
            ->withConfigure(
                <<<EOF
                ./autogen.sh && \
                ./configure --help &&  \
                CPPFLAGS="$(pkg-config  --cflags-only-I  --static libpng libjpeg )" \
                LDFLAGS="$(pkg-config --libs-only-L      --static libpng libjpeg )" \
                LIBS="$(pkg-config --libs-only-l         --static libpng libjpeg )" \
                ./configure --prefix={$libwebp_prefix} \
                --enable-static --disable-shared \
                --enable-libwebpdecoder \
                --enable-libwebpextras \
                --with-pngincludedir={$libpng_prefix}/include \
                --with-pnglibdir={$libpng_prefix}/lib \
                --with-jpegincludedir={$libjpeg_prefix}/include \
                --with-jpeglibdir={$jpeg_lib_dir} \
                --with-gifincludedir={$libgif_prefix}/include \
                --with-giflibdir={$libgif_prefix}/lib
EOF
            )
            ->withPkgName('libwebp')
            ->withLdflags('-L' . WEBP_PREFIX . '/lib -lwebpdemux -lwebpmux')
            ->depends('libpng', 'libjpeg', 'libgif')
    );
}


function install_libyuv(Preprocessor $p)
{
    $libyuv_prefix = "/usr/libyuv";
    $libjpeg_prefix = JPEG_PREFIX;
    $libjpeg_lib_dir = $p->getOsType()== 'linux' ? $libjpeg_prefix .'/lib64/' : $libjpeg_prefix .'/lib/';
    $p->addLibrary(
        (new Library('libyuv'))
            ->withUrl('https://chromium.googlesource.com/libyuv/libyuv')
            ->withHomePage('https://chromium.googlesource.com/libyuv/libyuv')
            ->withLicense('https://github.com/AOMediaCodec/libavif/blob/main/LICENSE', Library::LICENSE_SPEC)
            ->withManual('https://https://chromium.googlesource.com/libyuv/libyuv/+/HEAD/docs/getting_started.md')
            ->withSkipDownload()
            ->withUntarArchiveCommand('mv')
            ->withPrefix($libyuv_prefix)
            ->withCleanBuildDirectory()
            ->withCleanPreInstallDirectory($libyuv_prefix)
            ->withBuildScript(
                <<<EOF
            set -uex
            pwd
            ls -lh .
            cd libyuv


            ls -lh .

            make -f linux.mk
            mkdir -p $libyuv_prefix/lib
            cp -rf libyuv.a  $libyuv_prefix/lib
            cp -rf include $libyuv_prefix/

            exit  0
            gn gen out/Release "--args=is_debug=false"

            ninja -v -C out/Release
            exit  0


            #  cmake默认查找到的是动态库 ; cmake 优先使用静态库
            #  参考 https://blog.csdn.net/10km/article/details/82931978

            # sed -i '/find_package ( JPEG )/i set( JPEG_NAMES libjpeg.a )'  CMakeLists.txt

            # -DJPEG_LIBRARY_RELEASE={$libjpeg_prefix}/lib/libjpeg.a
            # CMAKE_INCLUDE_PATH 和 CMAKE_LIBRARY_PATH

            # -DJPEG_LIBRARY:PATH={$libjpeg_lib_dir}/libjpeg.a -DJPEG_INCLUDE_DIR:PATH={$libjpeg_prefix}/include/ \

            mkdir -p build
            cd build
            cmake \
            -Wno-dev \
            -DCMAKE_INSTALL_PREFIX="{$libyuv_prefix}" \
            -DCMAKE_BUILD_TYPE="Release"  \
            -DJPEG_LIBRARY:PATH={$libjpeg_lib_dir}/libjpeg.a -DJPEG_INCLUDE_DIR:PATH={$libjpeg_prefix}/include/ \
            -DBUILD_SHARED_LIBS=OFF  ..

            cmake --build . --config Release
            cmake --build . --target install --config Release


EOF
            )
            ->withPkgName('')
    );
}
function install_libavif(Preprocessor $p)
{
    $libavif_prefix = LIBAVIF_PREFIX;
    $p->addLibrary(
        (new Library('libavif'))
            ->withUrl('https://github.com/AOMediaCodec/libavif/archive/refs/tags/v0.11.1.tar.gz')
            ->withFile('libavif-v0.11.1.tar.g')
            ->withHomePage('https://aomediacodec.github.io/av1-avif/')
            ->withLicense('https://github.com/AOMediaCodec/libavif/blob/main/LICENSE', Library::LICENSE_SPEC)
            ->withManual('https://github.com/AOMediaCodec/libavif')
            ->withPrefix($libavif_prefix)
            ->withCleanBuildDirectory()
            ->withCleanPreInstallDirectory($libavif_prefix)
            ->withConfigure(
                <<<EOF
            CPPFLAGS="$(pkg-config  --cflags-only-I  --static libpng libjpeg )" \
            LDFLAGS="$(pkg-config --libs-only-L      --static libpng libjpeg )" \
            LIBS="$(pkg-config --libs-only-l         --static libpng libjpeg )" \
            cmake .  \
            -DCMAKE_INSTALL_PREFIX={$libavif_prefix} \
            -DAVIF_BUILD_EXAMPLES=ON \
            -DBUILD_SHARED_LIBS=OFF \
            -DAVIF_CODEC_AOM=OFF \
            -DAVIF_CODEC_DAV1D=OFF \
            -DAVIF_CODEC_LIBGAV1=OFF \
            -DAVIF_CODEC_RAV1E=OFF
            exit 0

EOF
            )
            ->withPkgName('libavif')
            ->withLdflags('')
    );
}


function install_libde265(Preprocessor $p)
{
    $libde265_prefix = LIBDE265_PREFIX;
    $lib = new Library('libde265');
    $lib->withHomePage('https://github.com/strukturag/libde265.git')
        ->withLicense('https://github.com/strukturag/libheif/blob/master/COPYING', Library::LICENSE_GPL)
        ->withUrl('https://github.com/strukturag/libde265/archive/refs/tags/v1.0.11.tar.gz')
        ->withFile('libde265-v1.0.11.tar.gz')

        ->withPrefix($libde265_prefix)
        ->withCleanBuildDirectory()
        ->withCleanPreInstallDirectory($libde265_prefix)
        ->withConfigure(
            <<<EOF
        ./autogen.sh
        ./configure --help
        ./configure --prefix={$libde265_prefix} \
        --enable-shared=no \
        --enable-static=yes
EOF
        )
        ->withPkgName('libde265');

    $p->addLibrary($lib);
}

function install_libheif(Preprocessor $p)
{
    $libheif_prefix = LIBHEIF_PREFIX;
    $lib = new Library('libheif');
    $lib->withHomePage('https://github.com/strukturag/libheif.git')
        ->withLicense('https://github.com/strukturag/libheif/blob/master/COPYING', Library::LICENSE_GPL)
        ->withUrl('https://github.com/strukturag/libheif/releases/download/v1.15.1/libheif-1.15.1.tar.gz')

        ->withPrefix($libheif_prefix)
        ->withCleanBuildDirectory()
        ->withCleanPreInstallDirectory($libheif_prefix)
        ->withConfigure(
            <<<'EOF'
            ./configure --help

            libde265_CFLAGS=$(pkg-config  --cflags --static libde265 ) \
            libde265_LIBS=$(pkg-config    --libs   --static libde265 ) \
            libpng_CFLAGS=$(pkg-config  --cflags --static libpng ) \
            libpng_LIBS=$(pkg-config    --libs   --static libpng ) \
EOF
            . PHP_EOL .
            <<<EOF
            ./configure \
            --prefix={$libheif_prefix} \
            --enable-shared=no \
            --enable-static=yes
EOF
        )
        ->withPkgName('libheif');

    $p->addLibrary($lib);
}


function install_graphite2(Preprocessor $p)
{
    $graphite2_prefix = "/usr/graphite2";
    $p->addLibrary(
        (new Library('graphite2'))
            ->withLicense('https://github.com/silnrsi/graphite/blob/master/COPYING', Library::LICENSE_SPEC)
            ->withHomePage('http://graphite.sil.org/')
            ->withUrl('https://github.com/silnrsi/graphite/archive/refs/tags/1.3.14.tar.gz')
            ->withManual('https://github.com/silnrsi/graphite.git')
            ->withFile('graphite-1.3.14.tar.gz')
            ->withLabel('library')
            ->withPrefix($graphite2_prefix)
            ->withCleanBuildDirectory()
            ->withConfigure(
                "
            mkdir -p build
            cd build
            cmake   ..  \
            -DCMAKE_INSTALL_PREFIX={$graphite2_prefix} \
            -DCMAKE_BUILD_TYPE=Release \
            -DBUILD_SHARED_LIBS=OFF
            "
            )
            ->withPkgName('graphite2')
    );
}

function install_libfribidi(Preprocessor $p)
{
    $libfribidi_prefix = LIBFRIBIDI_PREFIX;
    $p->addLibrary(
        (new Library('libfribidi'))
            ->withLicense('https://github.com/fribidi/fribidi/blob/master/COPYING', Library::LICENSE_LGPL)
            ->withHomePage('https://github.com/fribidi/fribidi.git')
            ->withUrl('https://github.com/fribidi/fribidi/archive/refs/tags/v1.0.12.tar.gz')
            ->withFile('fribidi-v1.0.12.tar.gz')
            ->withLabel('library')
            ->withPrefix($libfribidi_prefix)
            ->withCleanBuildDirectory()
            ->withCleanPreInstallDirectory($libfribidi_prefix)
            ->withConfigure(
                "

                # 可以使用 meson
                # meson setup  build

                sh autogen.sh
                ./configure --help

                ./configure \
                --prefix={$libfribidi_prefix} \
                --enable-static=yes \
                --enable-shared=no
            "
            )
            ->withPkgName('harfbuzz-icu  harfbuzz-subset harfbuzz')
    );
}
function install_harfbuzz(Preprocessor $p)
{
    $harfbuzz_prefix = HARFBUZZ_PREFIX;
    $p->addLibrary(
        (new Library('harfbuzz'))
            ->withLicense('https://github.com/harfbuzz/harfbuzz/blob/main/COPYING', Library::LICENSE_MIT)
            ->withHomePage('https://github.com/harfbuzz/harfbuzz.git')
            ->withUrl('https://github.com/harfbuzz/harfbuzz/archive/refs/tags/7.1.0.tar.gz')
            ->withFile('harfbuzz-7.1.0.tar.gz')
            ->withLabel('library')
            ->withPrefix($harfbuzz_prefix)
            ->withCleanBuildDirectory()
            ->withCleanPreInstallDirectory($harfbuzz_prefix)
            ->withBuildScript(
                "
                meson help
                meson setup --help

                meson setup  build \
                --backend=ninja \
                --prefix={$harfbuzz_prefix} \
                --default-library=static \
                -Dglib=disabled \
                -Dicu=enabled \
                -Dfreetype=disabled \
                -Dtests=disabled \
                -Ddocs=disabled  \
                -Dbenchmark=disabled

                meson compile -C build
                meson install -C build
                # ninja -C build
                # ninja -C build install

            "
            )
            ->withPkgName('harfbuzz-icu  harfbuzz-subset harfbuzz')
    );
}





function install_freetype(Preprocessor $p)
{
    $freetype_prefix = FREETYPE_PREFIX;
    $bzip2_prefix = BZIP2_PREFIX;
    $libpng_prefix = PNG_PREFIX;
    $libzlib_prefix = ZLIB_PREFIX;
    $p->addLibrary(
        (new Library('freetype'))
            ->withPrefix($freetype_prefix)
            ->withUrl('https://download.savannah.gnu.org/releases/freetype/freetype-2.10.4.tar.gz')
            ->withLicense(
                'https://gitlab.freedesktop.org/freetype/freetype/-/blob/master/docs/FTL.TXT',
                Library::LICENSE_SPEC
            )
            ->withCleanBuildDirectory()
            ->withCleanPreInstallDirectory($freetype_prefix)
            ->withConfigure(
                <<<EOF
            ./configure --help
            BZIP2_CFLAGS="-I{$bzip2_prefix}/include"  \
            BZIP2_LIBS="-L{$bzip2_prefix}/lib -lbz2"  \
            CPPFLAGS="$(pkg-config --cflags-only-I --static zlib libpng  harfbuzz libbrotlicommon  libbrotlidec  libbrotlienc)" \
            LDFLAGS="$(pkg-config  --libs-only-L   --static zlib libpng  harfbuzz libbrotlicommon  libbrotlidec  libbrotlienc)" \
            LIBS="$(pkg-config     --libs-only-l   --static zlib libpng  harfbuzz libbrotlicommon  libbrotlidec  libbrotlienc)" \
            ./configure --prefix={$freetype_prefix} \
            --enable-static \
            --disable-shared \
            --with-zlib=yes \
            --with-bzip2=yes \
            --with-png=yes \
            --with-harfbuzz=no  \
            --with-brotli=yes
EOF
            )
            ->withHomePage('https://freetype.org/')
            ->withPkgName('freetype2')
            ->depends('zlib', 'bzip2', 'libpng', 'brotli')
    );
}


//-lgd -lpng -lz -ljpeg -lfreetype -lm

function install_libgd2($p)
{
    $libgd_prefix = LIBGD_PREFIX;
    $libiconv_prefix = ICONV_PREFIX;
    $lib = new Library('libgd2');
    $lib->withHomePage('https://www.libgd.org/')
        ->withLicense('https://github.com/libgd/libgd/blob/master/COPYING', Library::LICENSE_SPEC)
        ->withUrl('https://github.com/libgd/libgd/releases/download/gd-2.3.3/libgd-2.3.3.tar.gz')
        ->withManual('https://github.com/libgd/libgd.git')
        ->withManual('https://libgd.github.io/pages/docs.html')
        ->withPrefix($libgd_prefix)
        ->withCleanBuildDirectory()
        ->withCleanPreInstallDirectory($libgd_prefix)
        ->withConfigure(
            <<<'EOF'
        # 下载依赖
         ./configure --help
         # -lbrotlicommon-static -lbrotlidec-static -lbrotlienc-static
        export CPPFLAGS="$(pkg-config  --cflags-only-I  --static zlib libpng freetype2 libjpeg  libturbojpeg libwebp  libwebpdecoder  libwebpdemux  libwebpmux  libbrotlicommon  libbrotlidec  libbrotlienc ) " \
        export LDFLAGS="$(pkg-config   --libs-only-L    --static zlib libpng freetype2 libjpeg  libturbojpeg libwebp  libwebpdecoder  libwebpdemux  libwebpmux  libbrotlicommon  libbrotlidec  libbrotlienc ) " \
        export LIBS="$(pkg-config      --libs-only-l    --static zlib libpng freetype2 libjpeg  libturbojpeg libwebp  libwebpdecoder  libwebpdemux  libwebpmux  libbrotlicommon  libbrotlidec  libbrotlienc ) " \

        echo $LIBS

EOF . PHP_EOL . <<<EOF
        ./configure \
        --prefix={$libgd_prefix} \
        --enable-shared=no \
        --enable-static=yes \
        --without-freetype \
        --with-libiconv-prefix={$libiconv_prefix}
         # --with-freetype=/usr/freetype \

:<<'_EOF_'
        mkdir -p build
        cd build
        cmake   ..  \
        -DCMAKE_INSTALL_PREFIX={$libgd_prefix} \
        -DCMAKE_BUILD_TYPE=Release \
        -DENABLE_GD_FORMATS=1 \
        -DENABLE_JPEG=1 \
        -DENABLE_TIFF=1 \
        -DENABLE_ICONV=1 \
        -DENABLE_FREETYPE=1 \
        -DENABLE_FONTCONFIG=1 \
        -DENABLE_WEBP=1 \
        -DENABLE_HEIF=1 \
        -DENABLE_AVIF=1 \
        -DENABLE_WEBP=1

        cmake --build . -- -j$(nproc)
        exit 0
        cmake --install .
_EOF_

EOF
        )
        ->withMakeInstallCommand('')
        ->withPkgName('libgd2');

    $p->addLibrary($lib);
}

function install_GraphicsMagick($p)
{
    $libiconv_prefix = ICONV_PREFIX;
    $GraphicsMagick_prefix = '/usr/GraphicsMagick';
    $lib = new Library('GraphicsMagick');
    $lib->withHomePage('http://www.graphicsmagick.org/index.html')
        ->withLicense('https://github.com/libgd/libgd/blob/master/COPYING', Library::LICENSE_SPEC)
        ->withUrl('https://jaist.dl.sourceforge.net/project/graphicsmagick/graphicsmagick/1.3.40/GraphicsMagick-1.3.40.tar.gz')
        ->withManual('http://www.graphicsmagick.org/README.html')
        ->withManual('http://www.graphicsmagick.org/INSTALL-unix.html')
        ->withPrefix($GraphicsMagick_prefix)
        ->withCleanBuildDirectory()
        ->withCleanPreInstallDirectory($GraphicsMagick_prefix)
        ->withConfigure(
            <<<'EOF'
        # 下载依赖
         ./configure --help
         # -lbrotlicommon-static -lbrotlidec-static -lbrotlienc-static
        export CPPFLAGS="$(pkg-config  --cflags-only-I  --static zlib libpng freetype2 libjpeg  libturbojpeg libwebp  libwebpdecoder  libwebpdemux  libwebpmux  libbrotlicommon  libbrotlidec  libbrotlienc ) " \
        export LDFLAGS="$(pkg-config   --libs-only-L    --static zlib libpng freetype2 libjpeg  libturbojpeg libwebp  libwebpdecoder  libwebpdemux  libwebpmux  libbrotlicommon  libbrotlidec  libbrotlienc ) " \
        export LIBS="$(pkg-config      --libs-only-l    --static zlib libpng freetype2 libjpeg  libturbojpeg libwebp  libwebpdecoder  libwebpdemux  libwebpmux  libbrotlicommon  libbrotlidec  libbrotlienc ) " \

        echo $LIBS

EOF . PHP_EOL . <<<EOF
        ./configure \
        --prefix={$GraphicsMagick_prefix} \
        --enable-shared=no \
        --enable-static=yes \
        --without-freetype \
        --with-libiconv-prefix={$libiconv_prefix}
         # --with-freetype=/usr/freetype \

EOF
        )
        ->withMakeInstallCommand('')
        ->withPkgName('GraphicsMagick');

    $p->addLibrary($lib);
}




function install_libtiff(Preprocessor $p)
{
    $libtiff_prefix = LIBTIFF_PREFIX;
    $lib = new Library('libtiff');
    $lib->withHomePage('http://www.libtiff.org/')
        ->withLicense('https://gitlab.com/libtiff/libtiff/-/blob/master/LICENSE.md', Library::LICENSE_SPEC)
        ->withUrl('http://download.osgeo.org/libtiff/tiff-4.5.0.tar.gz')
        ->withPrefix($libtiff_prefix)
        ->withCleanBuildDirectory()
        ->withCleanPreInstallDirectory($libtiff_prefix)
        ->withConfigure(
            <<<'EOF'
            ./configure --help
            package_names="zlib libjpeg libturbojpeg liblzma  libzstd libwebp  libwebpdecoder  libwebpdemux  libwebpmux"

            CPPFLAGS=$(pkg-config  --cflags-only-I --static $package_names ) \
            LDFLAGS=$(pkg-config   --libs-only-L   --static $package_names ) \
            LIBS=$(pkg-config      --libs-only-l   --static $package_names ) \
EOF
            . PHP_EOL .
            <<<EOF
            ./configure --prefix={$libtiff_prefix} \
            --enable-shared=no \
            --enable-static=yes \
            --disable-docs \
            --disable-tests

EOF
        )
        ->withPkgName('libtiff');

    $p->addLibrary($lib);
}


function install_libXpm(Preprocessor $p)
{
    $libXpm_prefix = LIBXPM_PREFIX;
    $lib = new Library('libXpm');
    $lib->withHomePage('https://github.com/freedesktop/libXpm.git')
        ->withLicense('https://github.com/freedesktop/libXpm/blob/master/COPYING', Library::LICENSE_SPEC)
        ->withUrl('https://github.com/freedesktop/libXpm/archive/refs/tags/libXpm-3.5.11.tar.gz')
        ->withFile('libXpm-3.5.11.tar.gz')
        ->withPrefix($libXpm_prefix)
        ->withCleanBuildDirectory()
        ->withCleanPreInstallDirectory($libXpm_prefix)
        ->withScriptBeforeConfigure(
            <<<EOF
         # 依赖 xorg-macros
         # 解决依赖
         # apk add util-macros
         # apk add libxpm-dev
EOF
        )
        ->withConfigure(
            <<<EOF
            ./autogen.sh
            ./configure --help
            ./configure --prefix={$libXpm_prefix} \
            --enable-shared=no \
            --enable-static=yes \
            --disable-docs \
            --disable-tests \
            --enable-strict-compilation

EOF
        )
        ->withPkgName('libXpm');

    $p->addLibrary($lib);
}


function install_libraw(Preprocessor $p)
{
    $libraw_prefix = LIBRAW_PREFIX;
    $lib = new Library('libraw');
    $lib->withHomePage('https://www.libraw.org/about')
        ->withLicense('http://www.gnu.org/licenses/lgpl-2.1.html', Library::LICENSE_LGPL)
        ->withUrl('https://www.libraw.org/data/LibRaw-0.21.1.tar.gz')

        ->withPrefix($libraw_prefix)
        ->withCleanBuildDirectory()
        ->withCleanPreInstallDirectory($libraw_prefix)
        ->withConfigure(
            <<<'EOF'
            ./configure --help
            # ZLIB_CFLAGS=$(pkg-config  --cflags --static zlib )
            # ZLIB_LIBS=$(pkg-config    --libs   --static zlib )


            package_names="zlib libjpeg libturbojpeg "
            CPPFLAGS=$(pkg-config  --cflags-only-I --static $package_names ) \
            LDFLAGS=$(pkg-config   --libs-only-L   --static $package_names ) \
            LIBS=$(pkg-config      --libs-only-l   --static $package_names ) \
            LIBS="-lstdc++" \
EOF
            . PHP_EOL .
            <<<EOF
            ./configure \
            --prefix={$libraw_prefix} \
            --enable-shared=no \
            --enable-static=yes \
            --enable-jpeg \
            --enable-zlib
EOF
        )
        ->withPkgName('librawc  libraw_r');

    $p->addLibrary($lib);
}



function install_libOpenEXR(Preprocessor $p)
{
    $libOpenEXR_prefix = '/usr/libOpenEXR';
    $lib = new Library('libOpenEXR');
    $lib->withHomePage('http://www.openexr.com/')
        ->withLicense('https://github.com/AcademySoftwareFoundation/openexr/blob/main/LICENSE.md', Library::LICENSE_BSD)
        ->withUrl('https://github.com/AcademySoftwareFoundation/openexr/archive/refs/tags/v3.1.5.tar.gz')
        ->withManual('https://github.com/AcademySoftwareFoundation/openexr.git')
        ->withManual('https://openexr.com/en/latest/install.html#install')
        ->withFile('openexr-v3.1.5.tar.gz')
        ->withPrefix($libOpenEXR_prefix)
        //->withCleanBuildDirectory()
        ->withCleanPreInstallDirectory($libOpenEXR_prefix)
        ->withBuildScript(
            <<<EOF
        # cmake .  -DCMAKE_INSTALL_PREFIX={$libOpenEXR_prefix}

        cmake.   --install-prefix={$libOpenEXR_prefix}
        cmake --build .  --target install --config Release
EOF
        )
        ->withPkgName('Imath OpenEXR')
        ->withBinPath('$libOpenEXR_prefix' . '/bin')
    ;

    $p->addLibrary($lib);
}

/**
 * @param Preprocessor $p
 * @return void
 * 并发编程：SIMD 介绍  https://zhuanlan.zhihu.com/p/416172020
 */
function install_highway(Preprocessor $p)
{
    $highway_prefix = '/usr/highway';
    $lib = new Library('highway');
    $lib->withHomePage('https://github.com/google/highway.git')
        ->withLicense('https://github.com/google/highway/blob/master/LICENSE', Library::LICENSE_APACHE2)
        ->withUrl('https://github.com/google/highway/archive/refs/tags/1.0.3.tar.gz')
        ->withFile('highway-1.0.3.tar.gz')
        ->withManual('https://github.com/google/highway.git')
        ->withPrefix($highway_prefix)
        ->withCleanBuildDirectory()
        ->withCleanPreInstallDirectory($highway_prefix)

        ->withConfigure(
            <<<EOF
# -DHWY_CMAKE_ARM7:BOOL=ON

# 会自动下载 googletest


    mkdir -p build && cd build
    cmake .. \
    -DCMAKE_INSTALL_PREFIX={$highway_prefix} \
    -DCMAKE_BUILD_TYPE=Release  \
    -DBUILD_SHARED_LIBS=OFF \
    -DHWY_FORCE_STATIC_LIBS=ON \
    -DBUILD_TESTING=OFF

EOF
        )
        ->withPkgName('libhwy-contrib.pc  libhwy-test.pc  libhwy');

    $p->addLibrary($lib);
}

function install_libjxl(Preprocessor $p)
{
    $libjxl_prefix = LIBJXL_PREFIX;
    $lib = new Library('libjxl');
    $lib->withHomePage('https://github.com/libjxl/libjxl.git')
        ->withLicense('https://github.com/libjxl/libjxl/blob/main/LICENSE', Library::LICENSE_BSD)
        ->withUrl('https://github.com/libjxl/libjxl/archive/refs/tags/v0.8.1.tar.gz')
        ->withManual('https://github.com/libjxl/libjxl/blob/main/BUILDING.md')
        ->withFile('libjpegxl-v0.8.1.tar.gz')
        ->withPrefix($libjxl_prefix)
        ->withCleanBuildDirectory()
        ->withCleanPreInstallDirectory($libjxl_prefix)
        ->withBuildScript(
            <<<EOF

        ## 会自动 下载依赖 ，如网速不佳，请在环境变量里设置代理地址，用于加速下载
        git init -b main .
        git add ./.gitmodules

        git -C . submodule update --init --recursive --depth 1 --recommend-shallow

        sh deps.sh

        git submodule update --init --recursive
        exit 0
        mkdir -p build
        cd build
        cmake -DJPEGXL_STATIC=true \
        -DCMAKE_BUILD_TYPE=Release \
        -DBUILD_SHARED_LIBS=OFF \
        -DBUILD_TESTING=OFF \
        -DCMAKE_INSTALL_PREFIX={$libjxl_prefix} \
         ..

        cmake --build . -- -j$(nproc)
        cmake --install .
EOF
        )
        ->withPkgName('libjxl');

    $p->addLibrary($lib);
}

/**
 *
 * HEIC是新出的一种图像格式 与JPG相比，它占用的空间更小，画质更加无损 HEIC使用的图像压缩编解码器最早是为视频开发的。
 * 高效视频编码（HEVC）用离散余弦和正弦变换（DCT和DST）压缩视频的每一帧
 * HEIC的效率是JPEG的两倍  作为iPhone的默认格式
 *
 * OpenEXR 视觉特效行业使用的一种文件格式,适用于高动态范围图像和HDR标准。 这种胶片格式具有适合电影制作的色彩保真度和动态范围
 *
 * openjp2
 * 参考文档：https://blog.csdn.net/Ruky_Z/article/details/100606195
 * openslide是处理医学图像， 医学图像最显著的一个特征就是“大”，如何处理这种“大”，目前常用的一种方法就是切割，将一个大的WSI切割成多个小tile，然后分别对多个tile进行处理，“化大为小”。
 *
 * 参考文档 ：https://zhuanlan.zhihu.com/p/504610500
 * JPEG XL 能在实现接近无损的视觉效果的同时，提供良好的压缩效果  它旨在超越现有的位图格式，并成为它们的通用替代
 *
 * 谷歌将专注于最终进一步推进 WebP 和 AVIF 图像格式
 *
 * @param Preprocessor $p
 * @return void
 */
function install_imagemagick(Preprocessor $p)
{
    $bzip2_prefix = BZIP2_PREFIX;
    $imagemagick_prefix = IMAGEMAGICK_PREFIX;
    $p->addLibrary(
        (new Library('imagemagick'))
            ->withHomePage('https://imagemagick.org/index.php')
            ->withUrl('https://github.com/ImageMagick/ImageMagick/archive/refs/tags/7.1.0-62.tar.gz')
            ->withLicense('https://imagemagick.org/script/license.php', Library::LICENSE_APACHE2)
            ->withManual('https://github.com/ImageMagick/ImageMagick.git')
            ->withPrefix($imagemagick_prefix)
            ->withCleanBuildDirectory()
            ->withCleanPreInstallDirectory($imagemagick_prefix)
            ->withFile('ImageMagick-v7.1.0-62.tar.gz')
            ->withPrefix($imagemagick_prefix)
            ->withConfigure(
                <<<EOF
            ./configure --help

            CPPFLAGS="$(pkg-config --cflags-only-I --static libzip zlib libzstd freetype2 libxml-2.0 liblzma openssl libjpeg  libturbojpeg libpng libwebp  libwebpdecoder  libwebpdemux  libwebpmux) -I{$bzip2_prefix}/include" \
            LDFLAGS="$(pkg-config  --libs-only-L   --static libzip zlib libzstd freetype2 libxml-2.0 liblzma openssl libjpeg  libturbojpeg libpng libwebp  libwebpdecoder  libwebpdemux  libwebpmux) -L{$bzip2_prefix}/lib" \
            LIBS="$(pkg-config     --libs-only-l   --static libzip zlib libzstd freetype2 libxml-2.0 liblzma openssl libjpeg  libturbojpeg libpng libwebp  libwebpdecoder  libwebpdemux  libwebpmux) -lbz2" \
            ./configure \
            --prefix={$imagemagick_prefix} \
            --enable-static \
            --disable-shared \
            --with-zip \
            --with-zlib \
            --with-lzma \
            --with-zstd \
            --with-jpeg \
            --with-png \
            --with-webp \
            --with-raw \
            --with-tiff \
            --with-xml \
            --with-freetype=yes \
            --enable-hdri \
            --enable-opencl \
            --without-djvu \
            --without-rsvg \
            --without-fontconfig \
            --without-heic \
            --without-jbig \
            --without-jxl \
            --without-lcms \
            --without-openjp2 \
            --without-lqr \
            --without-openexr \
            --without-pango

EOF
            )
            ->withPkgName('ImageMagick')
            ->depends(
                'libxml2',
                'libzip',
                'zlib',
                'libjpeg',
                'freetype',
                'libwebp',
                'libpng',
                'libgif',
                'openssl',
                'libzstd'
            )
    );
}
