<?php
/**
 * Author: Andrey Morozov
 * Date: 22.09.15
 */

namespace anmoroz\analytics\components;

use yii\base\ErrorException;

class ElasticaBase
{
    /**
     * @var string
     */
    private $host;
    /**
     * @var int
     */
    private $port;

    private $indexName;

    private $typeName;

    /**
     * @var \Elastica\Client
     */
    private $client;

    /**
     * @var \Elastica\Index
     */
    private $index;


    public function __construct($options, $indexName, $typeName)
    {
        if ($this->client) {
            return $this->client;
        }

        if ($options['debug']) {
            define('DEBUG', true);
        }

        $this->host = $options['host'];
        $this->port = $options['port'];

        $this->client = new \Elastica\Client([
            'host' => $this->host,
            'port' => $this->port
        ]);
        $this->index = $this->client->getIndex($indexName);
        $this->indexName = $indexName;
        $this->typeName = $typeName;
    }

    /**
     * @return bool
     */
    public function testConnection()
    {
        $connection = new \Elastica\Connection(['port' => $this->port]);
        $request = new \Elastica\Request('_status', \Elastica\Request::GET);
        $request->setConnection($connection);
        $result = true;
        try {
            $request->send();
        } catch (ErrorException $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * @param array $properties
     * @return \Elastica\Response
     */
    public function createMapping(array $properties)
    {
        $elasticaType = $this->index->getType($this->typeName);
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($elasticaType);
        $mapping->setProperties($properties);
        return $mapping->send()->isOk();
    }

    /**
     * Удаление индекса
     */
    public function deleteIndex()
    {
        if ($this->index->exists()) {
            $this->index->delete();
        }
    }

    /**
     * @return \Elastica\Client
     */
    public function getCient()
    {
        return $this->client;
    }

    /**
     * @return \Elastica\Index
     */
    public function getIndex()
    {
        return $this->index;
    }


    /**
     * @return \Elastica\Type
     */
    public function getType()
    {
        return $this->index->getType($this->typeName);
    }
}