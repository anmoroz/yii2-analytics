Модуль Yii2 для аналитики данных
------------

Модуль позволяет быстро и легко строить отчеты в различных разрезах.

![Схема работы](https://raw.github.com/anmoroz/yii2-analytics/master/docs/screenshots/yii2-analytics-schema.jpg)

##### Минимальные требования
* PHP >= 5.4.0.
* Elasticsearch 1.7.2
* Любая БД с вашими данными, поддерживаемая Yii2 (MySQL, MariaDB, SQLite, PostgreSQL, Oracle)

##### Зависимости
* yiisoft/yii2 ~2.0
* yiisoft/yii2-bootstrap ~2.0
* ruflin/elastica ~2.2.1
* bower-asset/select2 ~4.0

##### Установка

1. Установить Elasticsearch, следуя [документации](https://www.elastic.co/guide/en/elasticsearch/reference/current/setup.html).

2. Добавить модуль yii2-analytics в ваш Yii2 проект

```
php composer.phar require --prefer-dist anmoroz/yii2-analytics
```
или добавив
```json
"anmoroz/yii2-analytics": "~1.0.0"
```
в раздел require в вашем composer.json.

3. Создать компонент конфигуратора, согласно образцу AnalystsConfigurator.example.php. Разместить компонент можно в app\components вашего проекта.

4. Добавить в настройки приложения (web и console) модуль
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

Для console.php модуль необходимо добавить в секцию "bootstrap"
```php
'bootstrap' => ['analytics']
```

5. Выполнить команду `php yii analytics/indexation` для заполнения индекса данными. Примерное время индексации 650 тыс. позиций из образца - 2.5 мин.

6. Получить результат по ссылке /analytics

![Настройки](https://raw.github.com/anmoroz/yii2-analytics/master/docs/screenshots/settings_ru.jpg)

Поля группировки расположены по вертикали
![Отчет, поля по вертикали](https://raw.github.com/anmoroz/yii2-analytics/master/docs/screenshots/report_vertical_ru.jpg)

Поля группировки расположены по горизонтали
![Отчет, поля по горизонтали](https://raw.github.com/anmoroz/yii2-analytics/master/docs/screenshots/report_horizontal_ru.jpg)