### Usage
```
docker run \
--detach \
--name sensor-dashboard \
--publish 2876:2876 \
--env "HTTPD_SERVERNAME=**sub.do.main**" \
--volume sensor-dashboard-config:/config \
bmoorman/sensor-dashboard:latest
```
