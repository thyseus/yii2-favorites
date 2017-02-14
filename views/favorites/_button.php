<?php
use thyseus\favorites\models\Favorite;
use yii\helpers\Html;

if (!isset($owner))
    $owner = Yii::$app->user->id;

if (!isset($url))
    $url = null;

if (!isset($htmlOptions))
    $htmlOptions = [];

if (!isset($target_attribute))
    $target_attribute = null;

if ($favorite = Favorite::exists($model, $owner, $target)) {
    echo Html::a(sprintf(
        '<span data-toggle="popover" title="%s" data-content="%s"><span class="fa fa-star-o ci-color"></span></span>',
        Yii::t('app', 'Favorites'),
        Yii::t('app', 'Click to remove favorite')),
        ['/favorites/favorites/delete', 'id' => $favorite->id],
        $htmlOptions);
} else {
    echo Html::a(sprintf(
        '<span data-toggle="popover" title="%s" data-content="%s"><span class="fa fa-star ci-color"></span></span>',
        Yii::t('app', 'Favorites'),
        Yii::t('app', 'Click to add to favorites')),
        ['/favorites/favorites/create',
            'model' => $model,
            'target_id' => $target,
            'url' => $url,
            'target_attribute' => $target_attribute
        ],
        $htmlOptions);
}
