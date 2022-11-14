# Dockerfile created by MSRJ for api.sgs.ns2solution.com

# Base image
FROM ubuntu:20.04

# Set Locale and Encoding
RUN apt-get update && apt-get install -y locales && rm -rf /var/lib/apt/lists/* \
    && localedef -i en_US -c -f UTF-8 -A /usr/share/locale/locale.alias en_US.UTF-8
ENV LANG en_US.utf8

# Set Timezone
ENV TZ=Asia/Jakarta \
    DEBIAN_FRONTEND=noninteractive

# Install TZData
RUN apt-get update && apt-get install -y \
    tzdata 

# Install Dependencies    
RUN apt-get install -y \ 
    ubuntu-server \
    unzip \
    curl \
    build-essential \
    git \
    cron \
    php7.4-fpm \
    php7.4-common \
    php7.4-curl \
    php7.4-cli \
    php7.4-mysql \
    php7.4-gd \
    php7.4-xml \
    php7.4-json \
    php7.4-intl \
    php-pear \
    php7.4-dev \
    php7.4-mbstring \
    php7.4-zip \
    php7.4-soap \
    php7.4-bcmath \
    php7.4-opcache \ 
    php-redis \
    nginx \
    nano \
    certbot \
    python3-certbot-nginx
    
# Clear Cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Run Again
CMD hostnamectl set-hostname api.sgs.ns2solution.com

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '906a84df04cea2aa72f40b5f787e49f22d4c2f19492ac310e8cba5b96ac8b64115ac402c8cd292b8a03482574915d1a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

# Copy Source Code
COPY . /var/www/
COPY api.sgs.ns2solution.com /etc/nginx/sites-available/

# Nginx Stuff
RUN ln -s /etc/nginx/sites-available/api.sgs.ns2solution.com /etc/nginx/sites-enabled
RUN rm -rf /etc/nginx/sites-enabled/default
RUN service nginx restart 

# Set Working Directory
WORKDIR /var/www/

# Copy env
RUN mv env.example .env

# Install Project's Dependencies
RUN composer install
RUN composer update

# Setup Laravel
RUN php artisan key:generate
RUN php artisan cache:clear

# Set Folder Owner & Permissions
RUN chown -R www-data:www-data /var/www/
RUN find . -type f -exec chmod 664 {} \; 
RUN find . -type d -exec chmod 775 {} \;
# RUN chgrp -R www-data storage bootstrap/cache
# RUN chmod -R ug+rwx storage bootstrap/cache

# Ports
EXPOSE 80 443

# Run Services
ENTRYPOINT service nginx start && \
    service php7.4-fpm start && \
    /bin/bash