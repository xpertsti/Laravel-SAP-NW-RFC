# Imagen oficial de PHP 8.1 con FPM
FROM php:8.1-fpm

LABEL maintainer="Jorge Enrique Lopez <jorgeenriquelopezing@gmail.com>"

# Dependencias necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    libonig-dev \
    zip \
    unzip \
    git \
    autoconf \
    pkg-config \
    libssl-dev \
    libgmp-dev \
    libsodium-dev \
    build-essential

# Copiar el SDK de SAP NW RFC descomprimido localmente al contenedor
COPY ./nwrfcsdk_local /usr/sap/nwrfcsdk

# Crear el archivo de configuración para las bibliotecas del SDK de SAP NW RFC
RUN echo "/usr/sap/nwrfcsdk/lib" > /etc/ld.so.conf.d/nwrfcsdk.conf

# Ejecutar ldconfig para actualizar la cache de las bibliotecas
RUN ldconfig

# Verificar si el archivo sapnwrfc.h está presente
RUN if [ ! -f /usr/sap/nwrfcsdk/include/sapnwrfc.h ]; then \
        echo "sapnwrfc.h no se encontró. Verifica que el SDK esté descomprimido correctamente"; \
        exit 1; \
    fi

# Copiar el repositorio php-sapnwrfc clonado localmente al contenedor
COPY ./php-sapnwrfc /usr/src/php-sapnwrfc

# Compilar la extensión SAPNWRFC
RUN cd /usr/src/php-sapnwrfc && \
    phpize && \
    ./configure --with-sapnwrfc=/usr/sap/nwrfcsdk && \
    make && \
    make test && \
    make install

# Copiar el archivo php.ini al contenedor
COPY ./php.ini /usr/local/etc/php/conf.d/php.ini

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar los archivos del proyecto Laravel directamente al contenedor
COPY ./src /var/www

# Establecer el directorio de trabajo en /var/www
WORKDIR /var/www

# Listar el contenido del directorio para verificar que composer.json esté presente
RUN ls -la /var/www

# Ejecutar composer install para instalar dependencias de Laravel
RUN composer install

# Establecer permisos correctos para las carpetas necesarias
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Exponer el puerto 9000 para PHP-FPM
EXPOSE 9000

# Comando por defecto para PHP-FPM
CMD ["php-fpm"]
