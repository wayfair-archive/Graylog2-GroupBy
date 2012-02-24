# A Wayfair implementation of groupby with an ElasticSearch Backend (For Graylog2)

## Why?
* We wanted a way to generate statistics on similar data on the fly.
* 

## Configuration
* To use, copy the sample config (lib/config.php.sample) to lib/config.php and make any
changes you need
```php
# the hostname/ip to connect to ElasticSearch
define("ELASTICSEARCH_HOST", "localhost");
# the port to connect to ElasticSearch 
define("ELASTICSEARCH_PORT", "9200"); 
# the hostname to search for if none entered
define("DEFAULT_HOSTNAME", "*"); 
# the start date to search from if none entered
define("DEFAULT_START_DATE", "1 hour ago"); 
# the end date to search til if none entered
define("DEFAULT_END_DATE", "now"); 
# the defaults string to search for is none entered
define("DEFAULT_SEARCH_STRING", "logs"); 
```

## Included code
* [Bootstrap by Twitter](https://github.com/twitter/bootstrap/)
* [ElasticSearch by nervetattoe](https://github.com/nervetattoo/elasticsearch)

