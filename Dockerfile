# Development runtime for the OSAS Scholarship Allocation System.
#
# The application source is bind mounted by compose rather than copied in, so
# edits on the host take effect without a rebuild. That means this image only
# needs the PHP runtime and the extensions the app talks to a database with.
FROM php:8.4-cli-bookworm

# pdo_pgsql is the one that matters: it reaches both Supabase and the local
# postgres container. pdo_sqlite ships with the base image, which is what the
# test suite runs on.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        git \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libpq-dev \
        libzip-dev \
        qpdf \
        unzip \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        gd \
        intl \
        opcache \
        pdo_pgsql \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# PHP's built-in server is single threaded by default, so one slow page blocks
# every asset request queued behind it. Workers let them resolve in parallel.
ENV PHP_CLI_SERVER_WORKERS=4

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
