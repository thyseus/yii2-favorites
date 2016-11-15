<?php

namespace thyseus\favorites\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Html;

/**
 * This is the model class for table "favorite".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $offer_id
 *
 * @property Offer $offer
 */
class Favorite extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'favorites';
    }

    public static function bookmarkLink($model, $target_id, $url = null)
    {
        if ($favorite = Favorite::find()->where([
            'model' => $model,
            'created_by' => Yii::$app->user->id,
            'target_id' => $target_id,
        ])->one()
        )
            return Html::a(
                Yii::t('app', 'Remove favorite'),
                ['/favorites/favorites/delete', 'id' => $favorite->id],
                ['class' => 'btn btn-danger']
            );
        else
            return Html::a(
                Yii::t('app', 'Set as favorite'),
                ['/favorites/favorites/create', 'model' => $model, 'target_id' => $target_id, 'url' => $url],
                ['class' => 'btn btn-primary']
            );
    }

    public function behaviors()
    {
        return [
            BlameableBehavior::className(),
            [
                'class' => TimestampBehavior::className(),
                'value' => date('Y-m-d G:i:s'),
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['url', 'safe'],
            [['model', 'created_by', 'target_id'], 'unique',
                'targetAttribute' => ['model', 'created_by', 'target_id'],
                'message' => Yii::t('favorites', 'The Favorite already exists')]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('favorites', '#'),
            'created_by' => Yii::t('favorites', 'created by'),
            'updated_by' => Yii::t('favorites', 'updated by'),
            'created_at' => Yii::t('favorites', 'created at'),
            'updated_at' => Yii::t('favorites', 'updated at'),
            'model' => Yii::t('favorites', 'model'),
            'target_id' => Yii::t('favorites', 'Target'),
            'url' => Yii::t('favorites', 'url'),
        ];
    }

    /**
     * identifierAttribute is necessary e.g. for cases where the target model gets referenced by slug
     * @return \yii\db\ActiveQuery
     */
    public function getTarget()
    {
        $targetClass = $this->model;

        $target = new $targetClass;

        $identifier_attribute = 'id';

        if (method_exists($target, 'identifierAttribute'))
            $identifier_attribute = $target->identifierAttribute();

        return $this->hasOne($targetClass::className(), [$identifier_attribute => 'target_id']);
    }
}
