@echo off
rem Fast local dev server: PHP's built-in server runs on the CLI settings, where opcache is
rem off by default, so every request recompiles Laravel + vendor (1-4s). These flags turn it on.
set PHP_CLI_SERVER_WORKERS=4
php -d opcache.enable_cli=1 -d opcache.memory_consumption=256 -d opcache.interned_strings_buffer=16 -d opcache.max_accelerated_files=20000 -d opcache.validate_timestamps=1 -d opcache.revalidate_freq=2 -d opcache.jit=off -S 127.0.0.1:8000 -t public
