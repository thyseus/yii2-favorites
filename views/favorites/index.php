<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;


/* @var $this yii\web\View */
/* @var $searchModel app\models\SitecontentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('favorites', 'Favorites');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="favorites-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'created_at',
                'filter' => false,
                'format' => 'datetime',
            ],
            [
                'filter' => false,
                'format' => 'html',
                'attribute' => 'icon',
                'header' => '&nbsp;',
                'value' => function ($data) {
                    if ($data->icon)
                        return $data->icon;
                }
            ],
            [
                'filter' => $model_types,
                'format' => 'raw',
                'attribute' => 'model',
                'value' => function ($data) {
                    if ($data->model) {
                        $aliases = Yii::$app->getModule('favorites')->modelAliases;

                        return isset($aliases[$data->model]) ? $aliases[$data->model] : $data->model;
                    }
                }
            ],
            [
                'filter' => false,
                'format' => 'raw',
                'attribute' => 'target_id',
                'value' => function ($data) {
                    if ($data->target)
                        return Html::a($data->target->{$data->target_attribute}, $data->url, ['data-pjax' => 0]);
                }
            ],
            [
                'format' => 'raw',
                'attribute' => 'url',
                'value' => function ($data) {
                    return Html::a($data->url, $data->url, ['data-pjax' => 0]);
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete}',
                'urlCreator' => function ($action, $model, $key, $index) {
                    return Url::to(['favorites/' . $action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
