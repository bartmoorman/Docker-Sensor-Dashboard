### Docker Run
```
docker run \
--detach \
--name memcached \
memcached:latest

docker run \
--detach \
--name sensor-dashboard \
--link memcached \
--publish 2876:2876 \
--env "HTTPD_SERVERNAME=**sub.do.main**" \
--env "PUSHOVER_APP_TOKEN=azGDORePK8gMaC0QOYAMyEEuzJnyUi" \
--volume sensor-dashboard-config:/config \
bmoorman/sensor-dashboard:latest
```

### Docker Compose
```
version: "3.7"
services:
  memcached:
    image: memcached:latest
    container_name: memcached

   sensor-dashboard:
    image: bmoorman/sensor-dashboard:latest
    container_name: sensor-dashboard
    depends_on:
      - memcached
    ports:
      - "2876:2876"
    environment:
      - HTTPD_SERVERNAME=**sub.do.main**
      - PUSHOVER_APP_TOKEN=azGDORePK8gMaC0QOYAMyEEuzJnyUi
    volumes:
      - sensor-dashboard-config:/config

volumes:
  sensor-dashboard-config:
```
