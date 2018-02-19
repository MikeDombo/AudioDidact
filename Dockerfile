FROM php:7.2-apache
LABEL maintainer="Michael Dombrowski -- http://mikedombrowski.com"

RUN	apt-get update && apt-get install -yqq ffmpeg zip unzip curl gnupg2;
RUN	curl -sL https://deb.nodesource.com/setup_9.x | bash -
RUN	apt-get install -yqq nodejs
RUN	apt-get install -yqq libcurl4-openssl-dev pkg-config libssl-dev libpcre3-dev zlib1g-dev libbson-dev libmongoc-dev; \
	rm -rf /var/lib/apt/lists/*;

RUN	docker-php-ext-install pdo_mysql opcache;
RUN	pecl update-channels && pecl install mongodb && docker-php-ext-enable mongodb;

# set recommended PHP.ini settings
RUN { \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=2'; \
		echo 'opcache.fast_shutdown=1'; \
		echo 'opcache.enable_cli=1'; \
		echo 'max_execution_time=360'; \
		echo 'variables_order="EGPCS"'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini

RUN a2enmod rewrite expires headers && service apache2 restart

COPY src /var/www/html
COPY config.yml /var/www/config.yml

WORKDIR /var/www/
RUN	curl -sS https://getcomposer.org/installer | php; 

WORKDIR /var/www/html
RUN	rm -f composer.lock; \
	php /var/www/composer.phar install; \
	chown -R www-data:www-data /var/www/

EXPOSE 80/TCP

