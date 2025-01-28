# BUILD STAGE
FROM ubuntu:22.04 as builder

ARG NODE_VERSION=18
ARG POSTGRES_VERSION=14

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
    && apt-get install -y gnupg curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python2 \
    && mkdir -p ~/.gnupg \
    && chmod 600 ~/.gnupg \
    && echo "disable-ipv6" >> ~/.gnupg/dirmngr.conf \
    && echo "keyserver hkp://keyserver.ubuntu.com:80" >> ~/.gnupg/dirmngr.conf \
    && gpg --recv-key 0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c \
    && gpg --export 0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c > /usr/share/keyrings/ppa_ondrej_php.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu jammy main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y php8.2-cli php8.2-dev \
    php8.2-pgsql php8.2-sqlite3 php8.2-gd \
    php8.2-curl \
    php8.2-imap php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-zip php8.2-bcmath php8.2-soap \
    php8.2-intl php8.2-readline \
    php8.2-ldap \
    php8.2-msgpack php8.2-igbinary php8.2-redis php8.2-swoole \
    php8.2-memcached php8.2-pcov php8.2-xdebug \
    && php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y nodejs \
    && npm install -g npm \
    && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | tee /usr/share/keyrings/yarn.gpg >/dev/null \
    && echo "deb [signed-by=/usr/share/keyrings/yarn.gpg] https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list \
    && apt-get update \
    && apt-get install -y yarn \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.2

COPY composer.json .
COPY composer.lock .

COPY package.json .
# COPY yarn.lock .

RUN composer install --ignore-platform-reqs --no-scripts

RUN yarn install

COPY public public
COPY resources resources
COPY vite.config.js vite.config.js
COPY tailwind.config.js tailwind.config.js
COPY postcss.config.js postcss.config.js

RUN yarn build


# MAIN STAGE
FROM php:8.2-fpm as main

LABEL maintainer="Md Mohiuddin Mostafa Kamal Akib"

WORKDIR /var/www

# ARG WWWGROUP
ARG PHP_OPCACHE_VALIDATE_TIMESTAMPS
ARG SCHEDULED_NOTIFICATION_PRUNE_AGE
ARG PHP_OPCACHE_MAX_ACCELERATED_FILES
ARG PHP_OPCACHE_MEMORY_CONSUMPTION
ARG PHP_OPCACHE_MAX_WASTED_PERCENTAGE


ENV TZ=UTC

ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=${PHP_OPCACHE_VALIDATE_TIMESTAMPS} \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=${PHP_OPCACHE_MAX_ACCELERATED_FILES} \
    PHP_OPCACHE_MEMORY_CONSUMPTION=${PHP_OPCACHE_MEMORY_CONSUMPTION} \
    PHP_OPCACHE_MAX_WASTED_PERCENTAGE=${PHP_OPCACHE_MAX_WASTED_PERCENTAGE}

ENV APP_NAME=
ENV ASSET_URL=
ENV APP_ENV=
ENV APP_KEY=
ENV APP_DEBUG=
ENV APP_URL=
ENV SPA_RESET_PASSWORD_BASE_URL=
ENV LOG_CHANNEL=
ENV LOG_DEPRECATIONS_CHANNEL=
ENV LOG_LEVEL=
ENV DB_CONNECTION=
ENV DB_HOST=
ENV DB_PORT=
ENV DB_DATABASE=
ENV DB_USERNAME=
ENV DB_PASSWORD=
ENV BROADCAST_DRIVER=
ENV CACHE_DRIVER=
ENV QUEUE_CONNECTION=
ENV SESSION_DRIVER=
ENV SESSION_LIFETIME=
ENV REDIS_HOST=
ENV REDIS_PASSWORD=
ENV REDIS_PORT=
ENV MAIL_DRIVER=
ENV MAIL_HOST=
ENV MAIL_PORT=
ENV MAIL_USERNAME=
ENV MAIL_PASSWORD=
ENV MAIL_FROM_ADDRESS=
ENV MAIL_ENCRYPTION=
ENV MAIL_FROM_NAME=
ENV FILESYSTEM_DISK=
ENV AWS_ACCESS_KEY_ID=
ENV AWS_SECRET_ACCESS_KEY=
ENV AWS_DEFAULT_REGION=
ENV AWS_BUCKET=
ENV AWS_ENDPOINT=
ENV AWS_USE_PATH_STYLE_ENDPOINT=
ENV AWS_URL=
ENV SCOUT_DRIVER=
ENV MEILISEARCH_HOST=
ENV MEILISEARCH_NO_ANALYTICS=
ENV SCOUT_DRIVER=
ENV MEILISEARCH_HOST=
ENV MEILISEARCH_NO_ANALYTICS=
ENV PUSHER_APP_ID=
ENV PUSHER_APP_KEY=
ENV PUSHER_APP_SECRET=
ENV PUSHER_HOST=
ENV PUSHER_PORT=
ENV PUSHER_SCHEME=
ENV PUSHER_APP_CLUSTER=
ENV IMGPROXY_KEY=
ENV IMGPROXY_SALT=
ENV IMGPROXY_SECRET=
ENV IMGPROXY_URL=
ENV AUTHORIZATION_SERVER_URL=
ENV AUTHORIZATION_SERVER_CLIENT_ID=
ENV AUTHORIZATION_SERVER_CLIENT_SECRET=
ENV AUTHORIZATION_SERVER_TOKEN_URL=
ENV AUTHORIZATION_SERVER_INTROSPECT_URL=
ENV LIVEKIT_HOST=
ENV LIVEKIT_API_KEY=
ENV LIVEKIT_API_SECRET=
ENV FIREBASE_CREDENTIALS=
ENV SSLCZ_STORE_ID=
ENV SSLCZ_STORE_PASSWORD=
ENV SSLCZ_TESTMODE=
ENV SSLCZ_SUCCESS_URL=
ENV SSLCZ_FAIL_URL=
ENV SSLCZ_CANCEL_URL=
ENV SSLCZ_IPN_URL=
ENV PATHAO_API_BASE=
ENV PATHAO_CLIENT_ID=
ENV PATHAO_CLIENT_SECRET=
ENV PATHAO_CLIENT_EMAIL=
ENV PATHAO_CLIENT_PASSWORD=
ENV PATHAO_GRANT_TYPE=
ENV PATHAO_WEBHOOK_SECRET=

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
    && apt-get install -y nginx cron supervisor libedit-dev libsodium-dev libpcre3 libpcre3-dev libmhash2 libmhash-dev libssh2-1-dev libtidy-dev libxslt1-dev libmagic-dev libxslt-dev libexif-dev file libgd-dev libmagickwand-dev libmagick++-dev re2c libwebp-dev libjpeg-dev libxpm-dev libvpx-dev libpng++-dev libgmp-dev firebird-dev libib-util libcurl4-openssl-dev libldap2-dev libbz2-dev zlib1g-dev libicu-dev g++ libmcrypt-dev libssl-dev libssl-doc libsasl2-dev libzip-dev libxml2-dev libc-client-dev libkrb5-dev libpq-dev libz-dev libmemcached-dev build-essential libpng-dev libjpeg62-turbo-dev libfreetype6-dev locales zip jpegoptim optipng pngquant gifsicle vim unzip git curl\
    && apt-get update \
    && curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install extensions
RUN yes "" | pecl install msgpack igbinary redis memcached && \
    docker-php-ext-enable msgpack igbinary redis memcached
# RUN docker-php-ext-configure hash --with-mhash && \
# 	docker-php-ext-install hash
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install opcache pdo_pgsql pgsql imap xml zip bcmath soap intl ldap exif pcntl
RUN docker-php-ext-install mysqli pdo pdo_mysql
# install GD
RUN docker-php-ext-configure gd \
    --with-jpeg \
    --with-xpm \
    --with-webp \
    --with-freetype \
    && docker-php-ext-install -j$(nproc) gd

# RUN groupadd --force -g $WWWGROUP sail
# RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail

# Add user for laravel application
# RUN groupadd -g 1000 www
# RUN useradd -u 1000 -ms /bin/bash -g www www

# COPY vendor file from builder stage
COPY --from=builder /var/www/html/public public
COPY --from=builder /var/www/html/resources resources
COPY --from=builder /var/www/html/composer.json composer.json
COPY --from=builder /var/www/html/composer.lock composer.lock

RUN chmod -R ugo+rw /usr/bin/composer

RUN composer install --ignore-platform-reqs --no-dev --no-scripts

# COPY CODE FILES & FOLDERS
COPY app app
COPY bootstrap bootstrap
COPY config config
COPY database database
COPY routes routes
COPY storage storage
COPY artisan artisan

# add root to www group
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

COPY ./runtimes/8.2/start-container /usr/local/bin/start-container
COPY ./runtimes/8.2/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./runtimes/8.2/php.ini $PHP_INI_DIR/conf.d/99-sail.ini
COPY ./runtimes/8.2/nginx.conf /etc/nginx/sites-enabled/default
COPY ./runtimes/8.2/scheduler-crontab /etc/cron.d/scheduler-crontab

RUN chmod +x /usr/local/bin/start-container
RUN chmod 0644 /etc/cron.d/scheduler-crontab
RUN crontab /etc/cron.d/scheduler-crontab
RUN touch /var/log/cron.log
RUN touch /var/log/php-error.log
RUN touch /var/log/php-access.log
RUN touch /var/log/schedule.log
RUN touch /var/log/worker.log

EXPOSE 80 443

ENTRYPOINT ["start-container"]
