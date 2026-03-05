# Sử dụng PHP CLI (không FPM) vì chạy artisan serve
FROM php:8.4-cli

# Cài đặt các thư viện hệ thống cần thiết
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    vim \
    jpegoptim optipng pngquant gifsicle \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www

# Copy toàn bộ source code
COPY . /var/www

# Tạo user www-data (vì php-cli không có sẵn)
# UID/GID 33 là chuẩn cho www-data trong hầu hết image PHP
RUN groupadd -g 33 www-data \
    && useradd -u 33 -g www-data -m -s /bin/bash www-data \
    # Chown storage & cache cho user www-data
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Chạy container bằng user www-data (bảo mật hơn root)
USER www-data

# Mở port cho artisan serve
EXPOSE 8000

# Chạy Laravel built-in server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]