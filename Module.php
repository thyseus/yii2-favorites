<?php

namespace thyseus\favorites;

use Yii;
use yii\i18n\PhpMessageSource;

/**
 * Favorites module definition class
 */
class Module extends \yii\base\Module
{
    public $version = '0.1.0-dev';
    
    public $icon_active = '<i class="glyphicon glyphicon-star"></i>';
    public $icon_inactive = '<i class="glyphicon glyphicon-star-empty"></i>';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'thyseus\favorites\controllers';

    public $defaultRoute = 'favorites\favorites\index';

    /**
     * @var string The class of the User Model inside the application this module is attached to
     */
    public $userModelClass = 'app\models\User';

    /** @var array The rules to be used in URL management. */
    public $urlRules = [
        'favorites/update/<id>' => 'favorites/favorites/update',
        'favorites/delete/<id>' => 'favorites/favorites/delete',
        'favorites/<id>' => 'favorites/favorites/view',
        'favorites/index' => 'favorites/favorites/index',
        'favorites/create' => 'favorites/favorites/create',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!isset(Yii::$app->get('i18n')->translations['favorites*'])) {
            Yii::$app->get('i18n')->translations['favorites*'] = [
                'class' => PhpMessageSource::className(),
                'basePath' => __DIR__ . '/messages',
                'sourceLanguage' => 'en-US'
            ];
        }
        parent::init();
    }
}
