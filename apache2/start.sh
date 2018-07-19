#!/bin/bash
chown www-data: /config

if [ ! -d /config/sessions ]; then
    install -o www-data -g www-data -d /config/sessions
fi

if [ ! -d /config/httpd/ssl ]; then
    mkdir --parents /config/httpd/ssl
    ln --symbolic --force /etc/ssl/certs/ssl-cert-snakeoil.pem /config/httpd/ssl/dashboard.crt
    ln --symbolic --force /etc/ssl/private/ssl-cert-snakeoil.key /config/httpd/ssl/dashboard.key
fi

pidfile=/var/run/apache2/apache2.pid

if [ -f ${pidfile} ]; then
    pid=$(cat ${pidfile})

    if [ ! -d /proc/${pid} ] || [[ -d /proc/${pid} && $(basename $(readlink /proc/${pid}/exe)) != 'apache2' ]]; then
      rm ${pidfile}
    fi
fi

$(which memcached) \
    -l 127.0.0.1 \
    -d \
    -u memcache

$(which apache2ctl) \
    -D ${HTTPD_SECURITY:-HTTPD_SSL} \
    -D ${HTTPD_REDIRECT:-HTTPD_REDIRECT_SSL}

exec su \
    -c $(which notifications.php) \
    -s /bin/bash \
    www-data
