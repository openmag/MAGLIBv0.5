@ECHO OFF

RMDIR /S /Q magserver\local

RMDIR /S /Q magtest\log

DEL /F magtest\config.php
