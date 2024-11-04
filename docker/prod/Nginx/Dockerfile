FROM nginx:1.25.4-alpine

COPY ./docker/prod/Nginx/conf.d /etc/nginx/conf.d

WORKDIR /app

COPY ./public ./public

HEALTHCHECK --interval=5s --timeout=3s --start-period=1s CMD curl --fail http://0.0.0.0/health || exit 1
