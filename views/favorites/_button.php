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

if ($favorite = Favorite::exists($model, $owner, $target))
    echo Html::a(
        '<span data-toggle="popover" title="Merkliste" data-content="Klicken und aus der Merkliste entfernen"><span class="fa fa-star-o ci-color"></span></span>',
        ['/favorites/favorites/delete', 'id' => $favorite->id],
        $htmlOptions);
else
    echo Html::a(
        '<span data-toggle="popover" title="Merkliste" data-content="Klicken und zur Merkliste hinzufügen"><span class="fa fa-star ci-color"></span></span>',
        ['/favorites/favorites/create',
            'model' => $model,
            'target_id' => $target,
            'url' => $url,
            'target_attribute' => $target_attribute
        ],
        $htmlOptions);
?>
