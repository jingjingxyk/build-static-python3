#!/usr/bin/env bash

__DIR__=$(
  cd "$(dirname "$0")"
  pwd
)
__PROJECT__=$(
  cd ${__DIR__}/../../
  pwd
)
cd ${__PROJECT__}

GIT_BRANCH=$(git branch | grep '* ' | awk '{print $2}')
echo "current  git branch : "$GIT_BRANCH

if [ $GIT_BRANCH = 'new_dev' ]; then
  echo ' Deleting  folder is not allow in this branch : ' $GIT_BRANCH
  exit 0
fi

echo '正在执行删除无关的文件或者文件夹'

cd ${__PROJECT__}
test -f setup-aria2-runtime.sh && rm -rf setup-aria2-runtime.sh
test -f setup-coturn-runtime.sh && rm -rf setup-coturn-runtime.sh
test -f setup-ffmpeg-runtime.sh && rm -rf setup-ffmpeg-runtime.sh
test -f setup-go-runtime.sh && rm -rf setup-go-runtime.sh
test -f setup-nginx-runtime.sh && rm -rf setup-nginx-runtime.sh
test -f setup-nodejs-runtime.sh && rm -rf setup-nodejs-runtime.sh
test -f setup-php-cli-runtime.sh && rm -rf setup-php-cli-runtime.sh
test -f setup-php-fpm-runtime.sh && rm -rf setup-php-fpm-runtime.sh
test -f setup-privoxy-runtime.sh && rm -rf setup-privoxy-runtime.sh
test -f setup-socat-runtime.sh && rm -rf setup-socat-runtime.sh
test -f setup-supervisord.sh && rm -rf setup-supervisord.sh
test -f setup-swoole-cli-pre-runtime.sh && rm -rf setup-swoole-cli-pre-runtime.sh
test -f setup-webBenchmark-runtime.sh && rm -rf setup-webBenchmark-runtime.sh
test -f setup-swow-cli-runtime.sh && rm -rf setup-swow-cli-runtime.sh
test -f setup-php-fpm-7.4-runtime.sh && rm -rf setup-php-fpm-7.4-runtime.sh
test -f setup-swoole-cli-runtime.sh && rm -rf setup-swoole-cli-runtime.sh
test -f setup-php-cli-7.4-runtime.sh && rm -rf setup-php-cli-7.4-runtime.sh
test -f setup-php-cli-7.3-runtime.sh && rm -rf setup-php-cli-7.3-runtime.sh
test -f setup-swoole-cli-runtime.bat && rm -rf setup-swoole-cli-runtime.bat
test -f setup-swoole-cli-runtime.ps1 && rm -rf setup-swoole-cli-runtime.ps1
test -f setup-iperf3-runtime.sh && rm -rf setup-iperf3-runtime.sh
test -f setup-openssh-runtime.sh && rm -rf setup-openssh-runtime.sh
test -f setup-ttyd-runtime.sh && rm -rf setup-ttyd-runtime.sh
test -f setup-webdav-runtime.sh && rm -rf setup-webdav-runtime.sh
test -f setup-filebrowser-runtime.sh && rm -rf setup-filebrowser-runtime.sh
test -f setup-seaweedfs-runtime.sh && rm -rf setup-seaweedfs-runtime.sh
test -f setup-wstunnel-runtime.sh && rm -rf setup-wstunnel-runtime.sh
test -f drawdb.sh && rm -rf drawdb.sh
test -f drawio.sh && rm -rf drawio.sh
test -f geogebra.sh && rm -rf geogebra.sh
test -f setup-openjdk-runtime.sh && rm -rf setup-openjdk-runtime.sh
test -f three.js-editor.sh && rm -rf three.js-editor.sh
test -f swoole-docs.sh && rm -rf swoole-docs.sh
test -f setup-yt-dlp-ffmpeg-runtime.sh && rm -rf setup-yt-dlp-ffmpeg-runtime.sh
test -f setup-go-gost-runtime.sh && rm -rf setup-go-gost-runtime.sh
test -f setup-websocat-runtime.sh && rm -rf setup-websocat-runtime.sh
test -f setup-cloudreve-bin-runtime.sh && rm -rf setup-cloudreve-bin-runtime.sh
test -f setup-cloudreve-runtime.sh && rm -rf setup-cloudreve-runtime.sh
test -f setup-depot_tools.sh && rm -rf setup-depot_tools.sh
test -f setup-drawdb.sh && rm -rf setup-drawdb.sh
test -f setup-drawio.sh && rm -rf setup-drawio.sh
test -f setup-electron.sh && rm -rf setup-electron.sh
test -f setup-geogebra.sh && rm -rf setup-geogebra.sh
test -f setup-goreleaser-runtime.sh && rm -rf setup-goreleaser-runtime.sh
test -f setup-immich.sh && rm -rf setup-immich.sh
test -f setup-libcef.sh && rm -rf setup-libcef.sh
test -f setup-ninja-runtime.sh && rm -rf setup-ninja-runtime.sh
test -f setup-python3-runtime.sh && rm -rf setup-python3-runtime.sh
test -f setup-swoole-docs.sh && rm -rf setup-swoole-docs.sh
test -f setup-threejs-editor.sh && rm -rf setup-threejs-editor.sh

cd ${__PROJECT__}/
test -f .clang-format && rm -f .clang-format
test -f .gdbinit && rm -f .gdbinit
test -f sync-source-code.php && rm -f sync-source-code.php
test -f setup-runtime.md && rm -rf setup-runtime.md
test -f diff.php && rm -rf diff.php
test -f run-tests.php && rm -rf run-tests.php
test -f privoxy.sh && rm -rf privoxy.sh

cd ${__PROJECT__}/tools/
test -f ssh-d.sh && rm -rf ssh-d.sh
test -f ssh-j.sh && rm -rf ssh-j.sh
test -f ssh-l.sh && rm -rf ssh-l.sh
test -f ssh-r.sh && rm -rf ssh-r.sh
test -f socat-r.sh && rm -rf socat-r.sh
test -f download-runtime.sh && rm -rf download-runtime.sh
test -f download-coturn-artifacts.sh && rm -rf download-coturn-artifacts.sh
test -f download-nginx-artifacts.sh && rm -rf download-nginx-artifacts.sh
test -f download-openssh-artifacts.sh && rm -rf download-openssh-artifacts.sh
test -f download-privoxy-artifacts.sh && rm -rf download-privoxy-artifacts.sh
test -f download-socat-artifacts.sh && rm -rf download-socat-artifacts.sh
test -f download-ttyd-artifacts.sh && rm -rf download-ttyd-artifacts.sh

test -f upload-file-server/index.html && rm -rf upload-file-server/index.html
test -f upload-file-server/php.ini && rm -rf upload-file-server/php.ini
test -f upload-file-server/upload.php && rm -rf upload-file-server/upload.php
test -f upload-file-server/.gitignore && rm -rf upload-file-server/.gitignore
test -f upload-file-server/start-server.sh && rm -rf upload-file-server/start-server.sh

cd ${__PROJECT__}/sapi/
test -d build-dependencies-container && rm -rf build-dependencies-container
test -d tools && rm -rf tools
test -d webUI && rm -rf webUI
test -d lib && rm -rf lib
test -f SWOOLE-VERSION.conf && rm -f SWOOLE-VERSION.conf

cd ${__PROJECT__}/sapi/scripts/
test -f build-swoole-cli-alpine-container.sh && rm -rf build-swoole-cli-alpine-container.sh
test -f download-php-src-archive.php && rm -rf download-php-src-archive.php
test -f tencent-cloud-object-storage.sh && rm -rf tencent-cloud-object-storage.sh
test -f tencent-cloud-object-storage.yaml && rm -rf tencent-cloud-object-storage.yaml
test -f pack-sfx.php && rm -rf pack-sfx.php
test -f generate-artifact-hash.sh && rm -rf generate-artifact-hash.sh
test -f msys2-cygwin-install-depend.sh && rm -rf msys2-cygwin-install-depend.sh
test -f msys2/prepare-no-use.sh && rm -rf msys2/prepare-no-use.sh
test -f cygwin-install-depend.sh && rm -rf cygwin-install-depend.sh

cd ${__PROJECT__}/sapi/src/
test -d library_builder && rm -rf library_builder
test -d UnitTest && rm -rf UnitTest
test -d tests && rm -rf tests

cd ${__PROJECT__}/sapi/src/builder/
test -d library_shared && rm -rf library_shared

cd ${__PROJECT__}/sapi/docker/
test -d database && rm -rf database
test -d database-ui && rm -rf database-ui
test -d elasticsearch && rm -rf elasticsearch
test -d grafana && rm -rf grafana
test -d minio && rm -rf minio
test -d mysql && rm -rf mysql
test -d neo4j && rm -rf neo4j
test -d nginx && rm -rf nginx
test -d postgis && rm -rf postgis
test -d rabbitmq && rm -rf rabbitmq
test -d redis && rm -rf redis
test -d gitea && rm -rf gitea
test -d postgresql && rm -rf postgresql
test -d zerotier && rm -rf zerotier
test -d coturn && rm -rf coturn
test -d code-server && rm -rf code-server
test -d prometeus && rm -rf prometeus
test -d theia-ide && rm -rf theia-ide
test -f build-export-container.sh && rm -f build-export-container.sh
test -f docker-compose.md && rm -f docker-compose.md
test -f Dockerfile && rm -f Dockerfile
test -f README.md && rm -f README.md

cd ${__PROJECT__}/.github/workflows
test -f ceph.yml && rm -rf ceph.yml
test -f kubernetes.yml && rm -rf kubernetes.yml
test -f ovn.yml && rm -rf ovn.yml
test -f build-debian-builder-container.sh && rm -rf build-debian-builder-container.sh
test -f download-webrtc.yml && rm -rf download-webrtc.yml
test -f windows-native-2022.yml && rm -rf windows-native-2022.yml
test -f windows-native-vs2019.yml && rm -rf windows-native-vs2019.yml
test -f windows-native-vs2022.yml && rm -rf windows-native-vs2022.yml
test -f artifact-hash.yml && rm -rf artifact-hash.yml
test -f auto-cache-pool-tarball.yml && rm -rf auto-cache-pool-tarball.yml
test -f linux-mips64le.yaml && rm -rf linux-mips64le.yaml
test -f linux-riscv64.yml && rm -rf linux-riscv64.yml
test -f runner-images.md && rm -rf runner-images.md
test -f linux-aarch64-qemu.yml && rm -rf linux-aarch64-qemu.yml
test -f docker-install-push-to-gitee.yml && rm -rf docker-install-push-to-gitee.yml
test -f swoole-cli-push-to-gitee.yml && rm -rf swoole-cli-push-to-gitee.yml
test -f openssh-push-to-gitee.yml && rm -rf openssh-push-to-gitee.yml
test -f push-to-gitee.yml.bak && rm -rf push-to-gitee.yml.bak
test -f push-to-gitee.yml && rm -rf push-to-gitee.yml
test -f windows-native-vs2025.yml && rm -rf windows-native-vs2025.yml
test -f windows-native-2022.yml && rm -rf windows-native-2022.yml
test -f zerotier2.yml && rm -rf zerotier2.yml
test -f tailscale.yml && rm -rf tailscale.yml
test -f linux-loongarch.yaml && rm -rf linux-loongarch.yaml
test -f build-static-php-push-to-gitee.yml && rm -rf build-static-php-push-to-gitee.yml

cd ${__PROJECT__}/sapi/quickstart
test -d swoole-install && rm -rf swoole-install
test -f build-native-php-example.sh && rm -rf build-native-php-example.sh
test -f clean-no-match-library-for-php.sh && rm -rf clean-no-match-library-for-php.sh
test -f mark-install-library-cached.sh && rm -rf mark-install-library-cached.sh
test -d unix && rm -rf unix
test -d bsd && rm -rf bsd
test -f authors.sh && rm -f authors.sh
test -f deploy-git-proxy.sh && rm -f deploy-git-proxy.sh
test -f git-proxy-example.sh && rm -f git-proxy-example.sh

cd ${__PROJECT__}/sapi/quickstart/linux/
test -d SDS && rm -rf SDS
test -d kubernetes && rm -rf kubernetes
test -d qemu && rm -rf qemu
test -d SDN && rm -rf SDN
test -d pve && rm -rf pve

cd ${__PROJECT__}/sapi/quickstart/macos/

cd ${__PROJECT__}/sapi/quickstart/windows/
test -d native-build && rm -rf native-build
test -d openssh && rm -rf openssh
test -d UE5 && rm -rf UE5
test -f git-init.ps1 && rm -rf git-init.ps1
test -f http-proxy.bat && rm -rf http-proxy.bat
test -f http-proxy.ps1 && rm -rf http-proxy.ps1
test -f open-msys2-cygwin-website.bat && rm -rf open-msys2-cygwin-website.bat
test -f powershell-download.md && rm -rf powershell-download.md

cd ${__PROJECT__}

echo '删除完毕'
echo ''
