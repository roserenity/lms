# Local development image for the Laravel API and queue worker.
FROM php:8.3-cli

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip ghostscript \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions pdo_mysql gd zip bcmath intl exif pcntl redis imagick

RUN { \
        echo "upload_max_filesize = 1024M"; \
        echo "post_max_size = 1024M"; \
        echo "memory_limit = 1024M"; \
        echo "max_execution_time = 1800"; \
    } > /usr/local/etc/php/conf.d/lms.ini

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-scripts \
    && sed -i 's/\r$//' docker/entrypoint.sh \
    && chmod +x docker/entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--no-reload"]
