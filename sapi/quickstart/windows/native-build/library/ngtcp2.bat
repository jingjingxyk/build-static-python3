@echo off

setlocal
rem 显示当前脚本所在目录
echo %~dp0
cd %~dp0
cd ..\..\..\..\..\

set __PROJECT__=%cd%
cd /d %__PROJECT__%
mkdir  build

set CMAKE_BUILD_PARALLEL_LEVEL=%NUMBER_OF_PROCESSORS%

cd thirdparty\ngtcp2\
dir
mkdir  build-dir
cd build-dir
cmake .. ^
-DCMAKE_INSTALL_PREFIX="%__PROJECT__%\build\ngtcp2" ^
-DCMAKE_BUILD_TYPE=Release  ^
-DBUILD_SHARED_LIBS=OFF  ^
-DBUILD_STATIC_LIBS=ON

cmake --build . --config Release --target install


cd /d %__PROJECT__%
endlocal
