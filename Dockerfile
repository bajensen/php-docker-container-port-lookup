FROM bjensena/pag2471sc

COPY . /opt/docker-lookup/

WORKDIR /opt/docker-lookup/

RUN composer install

WORKDIR /opt/docker-lookup/public

EXPOSE 80

CMD ["php", "-S", "0.0.0.0:80"]
