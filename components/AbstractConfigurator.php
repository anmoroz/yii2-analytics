<?php
/**
 * Author: Andrey Morozov
 * Date: 18.09.15
 */

namespace anmoroz\analytics\components;

abstract class AbstractConfigurator
{

    const BELONGS_TO_CONDITION = 1;
    const BELONGS_TO_GROUPING = 2;
    const BELONGS_TO_AGGREGATION = 3;

    const ES_TYPE_STRING = 'string';
    const ES_TYPE_SHORT = 'short';
    const ES_TYPE_BOOLEAN = 'boolean';
    const ES_TYPE_INTEGER = 'integer';

    /**
     * ElasticSearch default index name
     */
    protected $esIndexName = 'analytics';

    /**
     * ElasticSearch default type name
     */
    protected $esTypeName = 'product';

    private $cachedFields = [];

    /**
     * The settings of all ElasticSearch fields
     *
     * @return array
     */
    abstract public function getProperties();

    /**
     * String scalar SQL query that returns the number of items analyzed data
     * Example: 'select count(*) from product'
     *
     * @return string
     */
    abstract public function getCountSQL();

    /**
     * String SQL query which SELECT section lists all the fields of the settings (method getProperties())
     *
     * @return string
     */
    abstract public function getIndexationSQL();

    /**
     * The name of the primary key, alias when used in a query getIndexationSQL()
     *
     * @return string
     */
    abstract public function getTablePrimaryKey();

    /**
     * Returns the fields that are involved in aggregation
     *
     * @return array
     */
    public function getFieldsByBelongs($belongs)
    {
        if (isset($this->cachedFields[$belongs])) {
            return $this->cachedFields[$belongs];
        }
        foreach ($this->getProperties() as $index => $property) {
            if (isset($property['belongs']) && in_array($belongs, $property['belongs'])) {
                $this->cachedFields[$belongs][$index] = $property;
            }
        }
        return $this->cachedFields[$belongs];
    }

    /**
     * @param array $fielfsList
     * @param string $index
     * @return array
     */
    public function extractValueByIndex(array $fielfsList, $index = 'name')
    {
        return array_map(
            function($value) use ($index) {
                return (isset($value[$index])) ? $value[$index] : '';
            },
            $fielfsList
        );
    }

    public function getIndexName()
    {
        return $this->esIndexName;
    }

    public function getTypeName()
    {
        return $this->esTypeName;
    }
}