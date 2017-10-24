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
 * @property string $icon
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
            ['icon', 'string'],
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
            'model' => Yii::t('favorites', 'type'),
            'target_id' => Yii::t('favorites', 'Target'),
            'url' => Yii::t('favorites', 'url'),
            'icon' => Yii::t('favorites', 'icon'),
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

    /**
     * Evaluate the current title of the Active Record Model that the Favorite links to.
     *
     * @return mixed|null the title if found, null otherwise. Null displays a nice, red - not set - in the default
     * DetailView and GridView, so this would look nice
     */
    public function targetTitle()
    {
        if (!$this->target) {
            return null;
        }

        $target = $this->target;

        if (method_exists($target, 'get' . ucfirst($this->target_attribute))) {
            return call_user_func([$target, 'get' . ucfirst($this->target_attribute)]);
        }

        if (isset($target->{$this->target_attribute})) {
            return $target->{$this->target_attribute};
        }

        return null;
    }

    public static function exists($model, $owner, $target)
    {
        return Favorite::findOne(['model' => $model, 'created_by' => $owner, 'target_id' => $target]);
    }
}
