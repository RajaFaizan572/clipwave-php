#!/bin/sh
set -e

# 1) Nginx: large request body allow (1 GB)
mkdir -p /etc/nginx/conf.d
cat > /etc/nginx/conf.d/99-uploads.conf <<'EOF'
# This file is included inside the http{} block
client_max_body_size 1024m;

# Push PHP ini values to FPM via fastcgi_param (global)
fastcgi_param PHP_VALUE "upload_max_filesize=1024M
post_max_size=1024M
max_execution_time=600
max_input_time=600
memory_limit=1024M";
EOF

# Optional: PHP upload temp dir (writable)
mkdir -p /home/tmp
chmod 777 /home/tmp

# Reload Nginx (ignore error if first boot)
nginx -t >/dev/null 2>&1 && nginx -s reload || true

# Try to gracefully reload php-fpm if signal is supported
if command -v killall >/dev/null 2>&1; then
  killall -USR2 php-fpm 2>/dev/null || true
fi

# Keep container alive (harmless)
sleep infinity
