FROM dunglas/frankenphp:php8.4-bookworm

# Install required PHP extensions
RUN install-php-extensions \
    pdo \
    pdo_mysql \
    mysqli \
    opcache \
    mbstring \
    fileinfo \
    gd

# Set working directory
WORKDIR /app

# Copy application files into the container
COPY . /app

# Create uploads directory and set permissions
RUN mkdir -p /app/uploads/bukti \
    && chmod -R 775 /app/uploads

# Configure Caddy to serve the PHP application from /app
ENV SERVER_NAME=":80"
ENV BASE_URL=""

# Write the Caddyfile for FrankenPHP
RUN cat > /etc/caddy/Caddyfile <<'EOF'
{
    frankenphp
    admin off
}

:80 {
    root * /app

    # Serve static files directly; route everything else through PHP
    @static {
        file
        not path *.php
    }
    handle @static {
        file_server
    }

    # Route all requests through PHP via FrankenPHP
    php_server
}
EOF

EXPOSE 80
