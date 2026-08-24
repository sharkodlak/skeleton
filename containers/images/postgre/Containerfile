FROM docker.io/library/postgres:18.6-alpine

ENV TZ=Europe/Prague \
    LANG=C.UTF-8 \
    LC_ALL=C.UTF-8

RUN apk add --no-cache \
        bash \
        tzdata \
    && ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime \
    && echo ${TZ} > /etc/timezone \
    && rm -rf /var/cache/apk/*
