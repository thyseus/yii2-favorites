<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Sitecontent */

$this->title = $model->target->{$model->target_attribute};
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sitecontent-view">

    <div class="row">
        <div class="row-lg-12">
            <h1><?= Html::encode($this->title) ?></h1>

            <p>
                <input action="action" type="button" class="btn btn-primary" value="<?= Yii::t('favorites', 'Back'); ?>" onclick="history.go(-1);" />

                <?= Html::a(Yii::t('favorites', 'Remove favorite'), ['/favorites/favorites/delete', 'id' => $model->id],
                    ['class' => 'btn btn-danger', 'data-confirm' => Yii::t('favorites', 'Are you sure?')]);

                ?>
            </p>
            <?php

            echo DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'target.' . $model->target_attribute,
                    'created_at',
                    [
                        'format' => 'html',
                        'attribute' => 'url',
                        'value' => Html::a($model->url, $model->url)
                    ]
                ]
            ]);
            ?>
        </div>
    </div>
</div>
