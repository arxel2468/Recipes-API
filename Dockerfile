FROM php:7.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql

# Configure PHP-FPM
RUN sed -i -e "s/;clear_env\s*=\s*no/clear_env = no/g" /usr/local/etc/php-fpm.d/www.conf

# Configure Nginx
COPY ./docker/nginx/default.conf /etc/nginx/sites-available/default
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log

# Set apps home directory
ENV APP_DIR /server/http

# Add the application code to the image
COPY . ${APP_DIR}

# Define current working directory
WORKDIR ${APP_DIR}

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Cleanup
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Setup startup script
COPY ./docker/startup.sh /startup.sh
RUN chmod +x /startup.sh

EXPOSE 80

CMD ["/startup.sh"]