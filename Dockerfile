FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install mysqli pdo_mysql pdo_pgsql pgsql zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www
COPY . .

RUN mkdir -p v1/storage/logs v2/storage/logs \
    && chmod -R 775 v1/storage v2/storage || true

EXPOSE 10000
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} -t /var/www"]
