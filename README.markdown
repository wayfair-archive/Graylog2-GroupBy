# A Wayfair implementation of groupby with an ElasticSearch Backend (For Graylog2)

## Why?
* We wanted a way to generate statistics on similar data on the fly.

## Configuration
* To use, copy the sample config (lib/config.php.sample) to lib/config.php and make any
changes you need

```php
<?php

define("ELASTICSEARCH_HOST", "localhost");
define("ELASTICSEARCH_PORT", "9200");
define("DEFAULT_HOSTNAME", "*");
define("DEFAULT_START_DATE", "1 hour ago");
define("DEFAULT_END_DATE", "now");
define("DEFAULT_SEARCH_STRING", "logs");
define("BASE", "/groupby");
```

## Included code
* [Bootstrap by Twitter](https://github.com/twitter/bootstrap/)
* [ElasticSearch by nervetattoe](https://github.com/nervetattoo/elasticsearch)

## Requirements
* php curl