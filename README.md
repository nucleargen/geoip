# geoip_test

## Preparation:
1. get maxmind geoip2 php reader
```
composer update
```

2. get maxmind geoip2 database, i.e. and put it into ./db folder

3. set up config with db name in index.php at line 9:

```
	'db'	=>	'db/DATABASE_FILENAME',
```

## Usage:
```
GET /?ip=x.x.x.x
```
Result:
```
{lat:XX,lon:YY,country:'country_name',city:'city_name'}
```
If no information about ip found - returns empty body with 404 http status

Request results caches for 30 minutes in ./cache folder