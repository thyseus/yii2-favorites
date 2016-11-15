<?php

namespace thyseus\favorites\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use thyseus\favorites\models\Favorite;

/**
 * FavoriteSearch represents the model behind the search form about `app\models\Favorite`.
 */
class FavoriteSearch extends Favorite
{
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Favorite::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]]
        ]);

        $this->load($params);

        $query->andFilterWhere([
            'created_by' => Yii::$app->user->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'url', $this->url]);

        return $dataProvider;
    }
}
