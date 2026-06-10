FROM php:8.2-apache

WORKDIR /var/www/html

COPY . .

RUN apt-get update && apt-get install -y \
    curl \
    libzip-dev

RUN docker-php-ext-install mysqli

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
CMD curl -f http://localhost/health.php || exit 1