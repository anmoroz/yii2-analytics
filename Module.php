<?php
/**
 * Author: Andrey Morozov
 * Date: 18.09.15
 */

namespace anmoroz\analytics;

use Yii;
use yii\base\Module as BaseModule;
use yii\base\BootstrapInterface;
use yii\di\ServiceLocator;
use yii\base\ErrorException;

class  Module extends BaseModule implements BootstrapInterface
{
    const VERSION = '0.0.1';

    public $controllerNamespace = 'anmoroz\analytics\controllers';

    /** @var \yii\di\ServiceLocator */
    private $locator;

    /** @var string */
    public $configClass;

    /** @var string */
    public $dbAdapterName = 'db';

    /** @var array ElasticSearch config */
    public $elasticSearch = [
        'host' => 'localhost',
        'port' => '9200',
        'debug' => false
    ];

    public function init()
    {
        parent::init();

        $this->locator = new ServiceLocator();

        $this->initConfigurator();
        $this->setDbAdapter();
        $this->initElastica();

        if (!isset(Yii::$app->get('i18n')->translations['analytics'])) {
            Yii::$app->get('i18n')->translations['analytics'] = [
                'class'    => \yii\i18n\PhpMessageSource::className(),
                'basePath' => __DIR__ . '/messages',
                'sourceLanguage' => 'en-US'
            ];
        }
    }

    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'anmoroz\analytics\commands';
        }
    }

    /**
     * @return array|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getProperties($index = null)
    {
        $properties = $this->locator->get('configurator')->getProperties();
        if (!is_null($index)) {
            return isset($properties[$index]) ? $properties[$index] : null;
        }
        return $properties;
    }

    /**
     * @return ServiceLocator
     */
    public function getServiceLocator()
    {
        return $this->locator;
    }

    private function initElastica()
    {
        $configurator = $this->locator->get('configurator');
        $this->locator->set('elastica', new \anmoroz\analytics\components\ElasticaBase(
            $this->elasticSearch,
            $configurator->getIndexName(),
            $configurator->getTypeName()
        ));
    }

    private function setDbAdapter()
    {
        if (
            !isset(Yii::$app->{$this->dbAdapterName})
            || ! Yii::$app->{$this->dbAdapterName} instanceof \yii\db\Connection
        ) {
            throw new ErrorException('DB adapter name is invalid. Please, set correct {dbAdapterName} variable');
        }

        $this->locator->set('db', Yii::$app->{$this->dbAdapterName});
    }

    private function initConfigurator()
    {
        if (!class_exists($this->configClass)) {
            throw new ErrorException('Analytics module is unable to find Configurator class. Please, set correct {configClass} variable');
        }

        $configurationComponent = new $this->configClass;
        if (! $configurationComponent instanceof \anmoroz\analytics\components\AbstractConfigurator
        ) {
            throw new ErrorException('DB adapter name is invalid. Please, set correct {dbAdapterName} variable');
        }

        $this->locator->set('configurator', $configurationComponent);
    }
}