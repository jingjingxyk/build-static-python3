<?php

use SwooleCli\Preprocessor;
use SwooleCli\Extension;

return function (Preprocessor $p) {
    $depends = [
        'socat'
    ];
    $ext = (new Extension('socat'))
        ->withHomePage('http://www.dest-unreach.org/socat/')
        ->withManual('http://www.dest-unreach.org/socat/')
        ->withLicense('https://repo.or.cz/socat.git/blob/refs/heads/master:/COPYING', Extension::LICENSE_LGPL);
    call_user_func_array([$ext, 'withDependentLibraries'], $depends);
    $p->addExtension($ext);
    $p->withReleaseArchive('socat', function (Preprocessor $p) {
        $workdir = $p->getWorkDir();
        $builddir = $p->getBuildDir();
        $socat_prefix = SOCAT_PREFIX;
        $system_arch=$p->getSystemArch();
        $cmd = <<<EOF
                mkdir -p {$workdir}/bin/
                cd {$builddir}/socat
                cp -f socat {$workdir}/bin/
                cp -rf doc {$workdir}/bin/socat-docs
                cd {$workdir}/bin/
                SOCAT_VERSION=$({$workdir}/bin/socat -V | grep 'socat version' | awk '{ print $3 }')
                echo \${SOCAT_VERSION} > {$workdir}/APP_VERSION
                strip {$workdir}/bin/socat

EOF;
        if ($p->getOsType() == 'macos') {
            $cmd .= <<<EOF
            otool -L {$workdir}/bin/socat
            tar -cJvf {$workdir}/socat-\${SOCAT_VERSION}-macos-{$system_arch}.tar.xz socat
EOF;
        } else {
            $cmd .= <<<EOF
              file {$workdir}/bin/socat
              readelf -h {$workdir}/bin/socat
              tar -cJvf {$workdir}/socat-\${SOCAT_VERSION}-linux-{$system_arch}.tar.xz socat

EOF;
        }
        return $cmd;
    });
};
