FROM bjensena/pag2471sc

COPY logs /opt/docker-lookup/
COPY public /opt/docker-lookup/
COPY src /opt/docker-lookup/
COPY view /opt/docker-lookup/
COPY composer.json /opt/docker-lookup/
COPY composer.lock /opt/docker-lookup/
COPY docker-entrypoint.sh /opt/docker-lookup/

WORKDIR /opt/docker-lookup/

RUN composer install

COPY conf/docker-lookup.conf /etc/httpd/conf.d/

RUN rm /etc/httpd/conf.d/welcome.conf && \
    chmod +x /opt/docker-lookup/docker-entrypoint.sh

WORKDIR /opt/docker-lookup/public

EXPOSE 80

ENTRYPOINT ["/opt/docker-lookup/docker-entrypoint.sh"]

#CMD ["php", "-S", "0.0.0.0:80"]
CMD ["httpd-foreground"]