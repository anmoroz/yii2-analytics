Yii2 Module for data analysts
============================

Construction of complex sections of data in tabular form.

Requirements
------------

* PHP >= 5.4.0.
* Elasticsearch 1.7.2
* yiisoft/yii2 ~2.0
* yiisoft/yii2-bootstrap ~2.0
* ruflin/elastica ~2.2.1
* bower-asset/select2 ~4.0

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist anmoroz/yii2-analytics
```

or add

```json
"anmoroz/yii2-analytics": "~1.0.0"
```

to the require section of your composer.json.

Configuration
-------------

Edit the configuration file (web.php), for example:

In section "module"
```php
'analytics' => [
    'class' => 'anmoroz\analytics\Module',
    'configClass' => 'app\components\AnalystsConfigurator',
    'dbAdapterName' => 'db',
    'elasticSearch' => [
        'host' => 'localhost',
        'port' => '9200',
        'debug' => false
    ]
]
```
Edit the configuration file (console.php) as web.php, and additionally in section "bootstrap"
```php
'bootstrap' => ['analytics']
```

Create AnalystsConfigurator exstends anmoroz\analytics\components\AbstractConfigurator

Indexing data
-------------

Execute the `php yii analytics/indexation` command

![yii2-analytics](https://cloud.githubusercontent.com/assets/6552104/10163376/32bfe786-66bb-11e5-8fe0-547e2a10dbdc.jpg)