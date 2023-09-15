FROM nginx:1.15-alpine

COPY ./docker/nginx/conf.d /etc/nginx/conf.d

WORKDIR /var/www/project

COPY . .
