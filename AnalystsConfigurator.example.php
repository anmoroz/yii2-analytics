<?php

namespace app\components;

use anmoroz\analytics\components\AbstractConfigurator;

class AnalystsConfigurator extends AbstractConfigurator
{
    /**
     * ElasticSearch index name
     * @var string
     */
    protected $esIndexName = 'analytics';

    /**
     * ElasticSearch type name
     * @var string
     */
    protected $esTypeName = 'product';

    /**
     * An array of settings search index
     *   Key element - a unique properties ID
     *   It is required to elements:
     *     "type" - string | shor | boolean | integer
     *     "belongs" - array, affiliations element
     *     "name" - name that is displayed in the form
     *
     * @return array
     */
    public function getProperties()
    {
        return [
            'brandId' => [
                'type' => self::ES_TYPE_STRING,
                'belongs' => [self::BELONGS_TO_CONDITION, self::BELONGS_TO_GROUPING],
                // ajax action, that returns entities in JSON format for Select2 form element
                // Example: [{"id":"3","text":"sony"},{"id":"207","text":"HP"}]
                'source' => ['ajaxUrl' => '/brand/suggestname'],
                'name' => 'Brand name',
                // SQL query for conversion in name of id
                'entityNameSQL' => 'SELECT name FROM product_brand WHERE id = :id'
            ],
            'storageSupplierStatus' => [
                'type' => self::ES_TYPE_STRING,
                'belongs' => [self::BELONGS_TO_CONDITION, self::BELONGS_TO_GROUPING],
                // Items for Html::dropDownList
                'source' => function() {
                    return ['Z' => 'Z', 'D' => 'D', 'S' => 'S'];
                },
                'name' => 'Storage supplier status'
            ],
            'issetImage' => [
                'type' => self::ES_TYPE_BOOLEAN,
                'belongs' => [self::BELONGS_TO_CONDITION],
                'name' => 'The presence of the image'
            ],
            'issetManufacturerLink' => [
                'type' => self::ES_TYPE_BOOLEAN,
                'belongs' => [self::BELONGS_TO_CONDITION],
                'name' => 'The presence of the manufacturer link'
            ],
            'issetCertificateFile' => [
                'type' => self::ES_TYPE_BOOLEAN,
                'belongs' => [self::BELONGS_TO_CONDITION],
                'name' => 'The presence of the certificate'
            ],
            'totalNumberProperties' => [
                'type' => self::ES_TYPE_INTEGER,
                'belongs' => [self::BELONGS_TO_AGGREGATION],
                'name' => 'The total number of properties'
            ],
            'totalNumberFilledProperties' => [
                'type' => self::ES_TYPE_INTEGER,
                'belongs' => [self::BELONGS_TO_AGGREGATION],
                'name' => 'The total number of filled properties'
            ],
            'percentageFilledFeatures' => [
                'type' => self::ES_TYPE_INTEGER,
                'belongs' => [self::BELONGS_TO_AGGREGATION],
                'name' => 'The percentage of filling properties'
            ]
        ];
    }

    /**
      * SQL query that returns the total number of entities
      *
      * @return string
      */
    public function getCountSQL()
    {
        return 'SELECT COUNT(*) FROM product';
    }

    /**
     * SQL query, that returns primary and all fields specified in the "getProperties" method of your database
     *
     * @return string
     */
    public function getIndexationSQL()
    {
        return 'SELECT
            ' . $this->getTablePrimaryKey() . ',
            brand.id as brandId,
            p.status as storageSupplierStatus,
            IF(p.image, 1, 0) as issetImage,
            IF(p.manufacturer_link, 1, 0) as issetManufacturerLink,
            (EXISTS (SELECT * FROM file WHERE product_id = p.id AND type = 2)) as issetCertificateFile,
            category.feature_count as totalNumberProperties,
            p.feature_count as totalNumberFilledProperties,
            IF(category.feature_count, round(p.feature_count * 100 / category.feature_count), 0) as percentageFilledFeatures
        FROM product p
        LEFT JOIN product_brand brand on (brand.id = p.brand_id)
        LEFT JOIN product_category category on (category.id = p.category_id)';
    }

    /**
     * Primary key with alias, if it's needed
     *
     * @return string
     */
    public function getTablePrimaryKey()
    {
        return 'p.id';
    }
}