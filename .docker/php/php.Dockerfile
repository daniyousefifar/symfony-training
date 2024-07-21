FROM php:8.2-fpm

# Change Linux Mirrors
RUN sed -i 's/deb.debian.org/ftp.tr.debian.org/g' '/etc/apt/sources.list'

# Copy composer files
COPY composer.lock composer.json /var/www/html

# Copy php-fpm config file
COPY ./.docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get -o Acquire::Check-Valid-Until=false update && apt-get install -y \
	build-essential \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libgd-dev \
    jpegoptim optipng pngquant gifsicle \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    zlib1g-dev \
    libzip-dev \
    pkg-config \
    libssl-dev \
    libmagickwand-dev \
    libpq-dev


# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install php extensions
RUN docker-php-ext-install bcmath curl pdo pdo_mysql soap xml mbstring exif pcntl zip intl

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pdo_pgsql pgsql

RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

RUN pecl install imagick
RUN docker-php-ext-enable imagick

RUN pecl install -o -f redis &&  rm -rf /tmp/pear
RUN docker-php-ext-enable redis

RUN docker-php-ext-install sockets

RUN pecl install excimer

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy exiting application directory contents
COPY . /var/www/html

# Copy exiting application directory permission
COPY --chown=www:www . /var/www/html/

# Change current user to www
USER www

# Expose port
EXPOSE 9081

# Start php-fpm server
CMD ["php-fpm"]
