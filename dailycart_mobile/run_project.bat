@echo off
setlocal

set "FLAVOR=%~1"
if "%FLAVOR%"=="" set "FLAVOR=customer"

if /I not "%FLAVOR%"=="customer" if /I not "%FLAVOR%"=="vendor" if /I not "%FLAVOR%"=="rider" (
  echo Usage: run_project.bat [customer^|vendor^|rider]
  exit /b 1
)

flutter run --flavor %FLAVOR% -t lib/main_%FLAVOR%.dart
