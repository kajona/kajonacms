#!/bin/bash
SERVER_ROOT="$PWD/../../"
echo "starting php built in webserver on 127.0.0.1:8080, document root $SERVER_ROOT"
php -S 127.0.0.1:8080 -t ${SERVER_ROOT}