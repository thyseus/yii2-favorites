<?php
use thyseus\favorites\models\Favorite;
use yii\helpers\Html;

if (!isset($owner))
    $owner = Yii::$app->user->id;

if (!isset($url))
    $url = null;

if (!isset($target_attribute))
    $target_attribute = null;

if ($favorite = Favorite::exists($model, $owner, $target))
    echo Html::a(Yii::t('app', 'Remove favorite'),
        ['/favorites/favorites/delete', 'id' => $favorite->id],
        ['class' => 'btn btn-danger']
    );
else
    echo Html::a(
        Yii::t('app', 'Set as favorite'),
        ['/favorites/favorites/create',
            'model' => $model,
            'target_id' => $target,
            'url' => $url,
            'target_attribute' => $target_attribute
        ],
        ['class' => 'btn btn-primary']
    );
?>
