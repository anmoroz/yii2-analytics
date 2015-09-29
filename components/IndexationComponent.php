<?php
/**
 * Author: Andrey Morozov
 * Date: 11.09.15
 */

namespace anmoroz\analytics\components;

use Yii;
use Elastica\Document;
use Elastica\Status;
use \Elastica\Client;
use yii\base\Object;
use anmoroz\analytics\traits\AnalyticsConfiguratorTrait;


class IndexationComponent extends Object
{
    use AnalyticsConfiguratorTrait;

    /**
     * The number of documents for indexing or other operations in the cycle for a single pass
     */
    const NUMBER_OF_DOCUMENTS_IN_LOOP = 100;

    /**
     * @var bool
     */
    public $dropIndex = false;

    /**
     * @var array
     */
    private $properties;

    /**
     * @var int
     */
    private $totalAddedDocuments = 0;

    public function init()
    {
        parent::init();
        $this->properties = self::getConfigurator()->getProperties();
    }

    public function run()
    {
        if ($this->dropIndex) {
            $this->elastica->deleteIndex();
        }

        if ($this->elastica->getIndex()->exists() === false) {
            $this->createIndex();
            $this->createMapping();
        }
        $itemsCount = self::getDBAdapter()->createCommand(
            self::getConfigurator()->getCountSQL()
        )->queryScalar();
        $loops = ceil($itemsCount / self::NUMBER_OF_DOCUMENTS_IN_LOOP);
        $nextId = 0;
        for ($i = 0; $i < $loops; $i++) {
            if ($i % 10 == 1) {
                gc_collect_cycles();
            }

            $nextId = $this->addItemsPart($nextId);
        }
    }

    /**
     * @return int
     */
    public function getTotalAddedDocuments()
    {
        return $this->totalAddedDocuments;
    }

    /**
     * @param int $nextId
     * @return int
     */
    private function addItemsPart($nextId)
    {
        $configurator = self::getConfigurator();
        $primaryKey = $configurator->getTablePrimaryKey();

        /** @var \yii\db\DataReader $reader */
        $dataReader = self::getDBAdapter()->createCommand(
            $configurator->getIndexationSQL()
            . ' where ' . $primaryKey . ' > ' . $nextId
            . ' order by ' . $primaryKey
            . ' limit ' . self::NUMBER_OF_DOCUMENTS_IN_LOOP
        )->query();

        $documents = [];
        $expoArray = explode('.', $primaryKey, 2);
        unset($primaryKey);
        $primaryKeyWithoutPrefix = array_pop($expoArray);
        $i = 0;
        while($row = $dataReader->read()) {
            $i++;
            if ($i === self::NUMBER_OF_DOCUMENTS_IN_LOOP) {
                $nextId = $row[$primaryKeyWithoutPrefix] + 1;
            }
            $documentId = $row[$primaryKeyWithoutPrefix];
            unset($row[$primaryKeyWithoutPrefix]);
            $documents[] = new Document($documentId, $row);
            $this->totalAddedDocuments++;
        }

        $elasticaType = self::getElastica()->getType();
        $elasticaType->addDocuments($documents);

        return $nextId;
    }

    private function createIndex()
    {
        // Create index
        $this->elastica->getIndex()->create([
            'number_of_shards' => 2,
            'number_of_replicas' => 0,
        ],true);
    }

    private function createMapping()
    {
        $properties = [];
        foreach ($this->properties as $key => $propertyArr) {
            $item = ['type' => $propertyArr['type']];
            if ($propertyArr['type'] == 'string' || $propertyArr['type'] == 'short') {
                $item['index'] = 'not_analyzed';
            }
            $properties[$key] = $item;
        }

        self::getElastica()->createMapping($properties);
    }
}