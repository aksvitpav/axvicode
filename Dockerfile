# ================================
# Development stage
# ================================
FROM php:8.4-fpm AS axvicode-development

ARG UID=1000
ARG GID=1000

ENV APPLICATION_DIRECTORY=/app

RUN apt-get update && apt-get install -y \
        build-essential pkg-config libcurl4-openssl-dev libxml2-dev \
        libonig-dev libzip-dev zlib1g-dev libpq-dev libfreetype-dev \
        libjpeg62-turbo-dev libpng-dev git curl zip unzip libicu-dev \
        supervisor \
    && docker-php-ext-install -j$(nproc) pcntl curl xml mbstring zip pdo pdo_pgsql bcmath gd intl \
    && pecl install xdebug-3.4.2 redis \
    && docker-php-ext-enable xdebug redis

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN addgroup --gid ${GID} laravel \
    && adduser --disabled-password --gecos "" --uid ${UID} --gid ${GID} laravel

WORKDIR $APPLICATION_DIRECTORY

COPY docker/entrypoint.dev.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

COPY docker/supervisord/dev/ /etc/supervisor/

COPY . .

RUN npm install

RUN chown -R ${UID}:${GID} $APPLICATION_DIRECTORY

USER laravel

EXPOSE 8000
EXPOSE 5173

CMD ["/entrypoint.sh"]
