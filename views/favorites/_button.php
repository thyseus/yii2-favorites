<?php
use thyseus\favorites\models\Favorite;
use yii\helpers\Html;
use yii\helpers\Url;

$icon_active = Yii::$app->getModule('favorites')->icon_active;
$icon_inactive = Yii::$app->getModule('favorites')->icon_inactive;

if (!isset($owner))
    $owner = Yii::$app->user->id;

if (!isset($url))
    $url = null;

if (!isset($htmlOptions))
    $htmlOptions = [];

if (!isset($target_attribute))
    $target_attribute = null;

if ($favorite = Favorite::exists($model, $owner, $target))
    echo Html::a(
        '<span data-toggle="popover" title="Merkliste" data-content="Klicken und aus der Merkliste entfernen">' . $icon_active . '</span></span>',
        ['/favorites/favorites/delete', 'id' => $favorite->id],
        $htmlOptions);
else
    echo Html::a(
        '<span data-toggle="popover" title="Merkliste" data-content="Klicken und zur Merkliste hinzufügen">' . $icon_inactive . '</span>',
        ['/favorites/favorites/create',
            'model' => $model,
            'target_id' => $target,
            'url' => $url,
            'target_attribute' => $target_attribute
        ],
        $htmlOptions);
