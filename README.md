Store and display readings from [Sensor](https://github.com/iVirus/Docker-Sensor)

### Docker Run
```
docker run \
--detach \
--name memcached \
--restart unless-stopped \
memcached:latest

docker run \
--detach \
--name sensor-dashboard \
--restart unless-stopped \
--link memcached \
--publish 2876:2876 \
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
    restart: unless-stopped

   sensor-dashboard:
    image: bmoorman/sensor-dashboard:latest
    container_name: sensor-dashboard
    restart: unless-stopped
    depends_on:
      - memcached
    ports:
      - "2876:2876"
    volumes:
      - sensor-dashboard-config:/config

volumes:
  sensor-dashboard-config:
```

### Environment Variables
|Variable|Description|Default|
|--------|-----------|-------|
|TZ|Sets the timezone|`America/Denver`|
|HTTPD_SERVERNAME|Sets the vhost servername|`localhost`|
|HTTPD_PORT|Sets the vhost port|`2876`|
|HTTPD_SSL|Set to anything other than `SSL` (e.g. `NO_SSL`) to disable SSL|`SSL`|
|HTTPD_REDIRECT|Set to anything other than `REDIRECT` (e.g. `NO_REDIRECT`) to disable SSL redirect|`REDIRECT`|
|PUSHOVER_APP_TOKEN|Used to retrieve sounds from the Pushover API|`<empty>`|
|MEMCACHED_HOST|Sets the Memcached host|`memcached`|
|MEMCACHED_PORT|Sets the Memcached port|`11211`|
|TEMPERATURE_SCALE|Sets the temperature scale (`celsius` (`c`), `fahrenheit` (`f`), or `kelvin` (`k`)) used to display readings|`celsius`|
