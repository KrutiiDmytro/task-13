<?php

echo 'PHP: ', PHP_VERSION, PHP_EOL;
echo 'Xdebug loaded: ', extension_loaded('xdebug') ? 'yes' : 'no', PHP_EOL;
echo 'Xdebug version: ', phpversion('xdebug') ?: '-', PHP_EOL;
echo 'xdebug.mode=', ini_get('xdebug.mode') ?: '-', PHP_EOL;
echo 'opcache.enable_cli=', ini_get('opcache.enable_cli') ?: '-', PHP_EOL;
