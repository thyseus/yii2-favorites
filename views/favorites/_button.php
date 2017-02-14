<?php
use thyseus\favorites\models\Favorite;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

if (!isset($owner))
    $owner = Yii::$app->user->id;

if (!isset($url))
    $url = null;

if (!isset($target_attribute))
    $target_attribute = null;

$necessaryOptions = [
    'class' => 'favorite-button',
    'data-model' => $model,
    'data-target-id' => $target,
    'data-url' => $url,
    'data-target_attribute' => $target_attribute,
    'data-pjax' => 0,
    'style' => 'cursor: pointer;',
];

if (!isset($htmlOptions))
    $htmlOptions = $necessaryOptions;
else
    $htmlOptions = array_merge($htmlOptions, $necessaryOptions);

if ($favorite = Favorite::exists($model, $owner, $target)) {
    echo Html::a(sprintf(
        '<span data-toggle="popover" title="%s" data-content="%s"><span class="fa fa-star-o ci-color"></span></span>',
        Yii::t('app', 'Favorites'),
        Yii::t('app', 'Click to remove favorite')), null, array_merge($htmlOptions, ['data-status' => 'active', 'data-id' => $favorite->id]));
} else {
    echo Html::a(sprintf(
        '<span data-toggle="popover" title="%s" data-content="%s"><span class="fa fa-star ci-color"></span></span>',
        Yii::t('app', 'Favorites'),
        Yii::t('app', 'Click to add to favorites')), null, array_merge($htmlOptions, ['data-status' => 'inactive']));
}

$url_create = Url::to(['/favorites/favorites/create']);
$url_remove = Url::to(['/favorites/favorites/delete']);

$this->registerJs("
    $('body').on('click', 'a.favorite-button', function(event) {
        data = {
             'model': $(this).data('model'),
             'target-id': $(this).data('target-id'),
             'url': $(this).data('url'),
             'target-attribute': $(this).data('target-attribute'),
             'id': $(this).data('id'),
        };

        if($(this).data('status') == 'active') {
            $.post({
                url: '$url_remove',
                data: data,
                context: this,
                success: function(result) {
                    $(this).replaceWith(result);
                    if(typeof afterFavoritesAjaxSuccess === 'function')
                        afterFavoritesAjaxSuccess();
                },
              });
        } else if($(this).data('status') == 'inactive') {
            $.post({
                url: '$url_create',
                data: data,
                context: this,
                success: function(result) {
                    $(this).replaceWith(result);
                    if(typeof afterFavoritesAjaxSuccess === 'function')
                        afterFavoritesAjaxSuccess();
                },
              });
        }
    });
", View::POS_READY, 'yii2-favorites-ajax-button'); // keep third parameter(id) to avoid unnecessary js burden
