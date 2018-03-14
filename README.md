# geoip_test
test case

Preparation:
1. get maxmind geoip2 php reader
```
composer update
```

2. get maxmind geoip2 database, i.e. 

Usage:
```
GET /?ip=x.x.x.x
```
Result:
```
{lat:XX,lon:YY,country:'country_name',city:'city_name'}
```
If no information about ip found - returns empty body with 404 http status
Request results caches for 30 minutes in ./cache folder