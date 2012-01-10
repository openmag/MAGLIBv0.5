@ECHO off

php --help > NUL 2> NUL 
if errorlevel 1 goto PHPNOTINPATH

php magserver\pushserv\checkos.php

if errorlevel 1 goto END

MKDIR magserver\local > NUL 2> NUL
MKDIR magtest\log > NUL 2> NUL
COPY  /Y magtest\config.php.example magtest\config.php > NUL 2> NUL

ECHO Prepare environment success...

GOTO END

:PHPNOTINPATH

ECHO Cannot find "php" in executive paths!
ECHO Quit...

:END
