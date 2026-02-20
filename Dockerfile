FROM php:8.2-fpm

WORKDIR /app

COPY ./src ./src
COPY ./src/.env .env
COPY ./src/schema.sql ./schema.sql

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
#to read/write PNG images
libpng-dev \
#to read/write JPEG images
libjpeg-dev \
#to read/write text using TrueType fonts
	libfreetype6-dev \
	#to connect to MySQL databases
	&& docker-php-ext-configure gd \
	--with-freetype \
	--with-jpeg \
	&& docker-php-ext-install gd pdo pdo_mysql \
	&& rm -rf /var/lib/apt/lists/*
	
RUN apt-get update && apt-get install -y msmtp \
	&& rm -rf /var/lib/apt/lists/*

COPY ./src/config/msmtprc /etc/msmtprc
RUN chmod 644 /etc/msmtprc
	
COPY ./src/config/php.ini /usr/local/etc/php/conf.d/php.ini

# just in case
RUN mkdir -p src/public/uploads
RUN chmod -R 775 src/public/uploads
