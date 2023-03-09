FROM php:8.1-fpm

# Arguments defined in docker-compose.yml
ARG user
ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    librabbitmq-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd sockets

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Pickle
ENV PICKLE_VERSION=0.7.11
RUN curl -Lo /usr/local/bin/pickle https://github.com/FriendsOfPHP/pickle/releases/download/v$PICKLE_VERSION/pickle.phar && \
    chmod +x /usr/local/bin/pickle

# Install Pickle Extensions
RUN pickle install --defaults redis \
    && pickle install --defaults amqp \
    && docker-php-ext-enable redis amqp

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

USER $user
