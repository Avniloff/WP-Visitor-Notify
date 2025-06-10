FROM wordpress:latest

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl zip pdo_mysql

# Install newer version of GeoIP functionality using MaxMind GeoIP2
RUN apt-get install -y libmaxminddb-dev \
    && pecl install maxminddb \
    && docker-php-ext-enable maxminddb

# Install Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Copy Xdebug configuration
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Copy Apache configuration to suppress ServerName warning
COPY apache-config.conf /etc/apache2/conf-available/servername.conf
RUN a2enconf servername

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Set working directory
WORKDIR /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Clean up
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
  CMD curl -f http://localhost/ || exit 1
