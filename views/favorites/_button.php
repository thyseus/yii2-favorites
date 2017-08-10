<?php
use thyseus\favorites\models\Favorite;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

if (!isset($owner)) {
    $owner = Yii::$app->user->id;
}

if (!isset($url)) {
    $url = null;
}

if (!isset($label_add) || $label_add === null) {
    $label_add = null;
}

if (!isset($label_remove) || $label_remove === null) {
    $label_remove = null;
}

if (!isset($label_tooltip_title_add) || $label_tooltip_title_add === null) {
    $label_tooltip_title_add = Yii::t('app', 'Favorites');
}

if (!isset($label_tooltip_title_remove) || $label_tooltip_title_remove === null) {
    $label_tooltip_title_add = Yii::t('app', 'Favorites');
}

if (!isset($label_tooltip_content_add) || $label_tooltip_content_add === null) {
    $label_tooltip_content_add = Yii::t('app', 'Click to add to favorites');
}

if (!isset($label_tooltip_content_remove) || $label_tooltip_content_remove === null) {
    $label_tooltip_content_remove = Yii::t('app', 'Click to remove favorite');
}

if (!isset($target_attribute)) {
    $target_attribute = null;
}

if (!isset($icon)) {
    $icon = '<i class="glyphicon glyphicon-star"></i>';
}

$necessaryOptions = [
    'class' => 'favorite-button',
    'data-model' => $model,
    'data-target-id' => $target,
    'data-url' => $url,
    'data-target_attribute' => $target_attribute,
    'data-pjax' => 0,
    'style' => 'cursor: pointer;',
];

if (!isset($htmlOptions)) {
    $htmlOptions = $necessaryOptions;
} else {
    $htmlOptions = array_merge($htmlOptions, $necessaryOptions);
}

if ($favorite = Favorite::exists($model, $owner, $target)) {
    echo Html::a(sprintf('<span data-toggle="popover" title="%s" data-content="%s">%s%s',
        $label_tooltip_title_remove,
        $label_tooltip_content_remove,
        Yii::$app->getModule('favorites')->icon_inactive,
        $label_remove), null, array_merge($htmlOptions, ['data-status' => 'active', 'data-id' => $favorite->id]));
} else {
    echo Html::a(sprintf('<span data-toggle="popover" title="%s" data-content="%s">%s%s',
        $label_tooltip_title_add,
        $label_tooltip_content_add,
        Yii::$app->getModule('favorites')->icon_active,
        $label_add), null, array_merge($htmlOptions, ['data-status' => 'inactive']));
}

$url_create = Url::to(['/favorites/favorites/create']);
$url_remove = Url::to(['/favorites/favorites/delete']);

$this->registerJs("
    $('body').on('click', 'a.favorite-button', function(event) {
        data = {
             'model': $(this).data('model'),
             'icon': '" . $icon . "',
             'target-id': $(this).data('target-id'),
             'url': $(this).data('url'),
             'target-attribute': $(this).data('target-attribute'),
             'id': $(this).data('id'),
             'label_add': '$label_add',
             'label_remove': '$label_remove',
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
