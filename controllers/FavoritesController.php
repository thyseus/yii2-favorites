<?php

namespace thyseus\favorites\controllers;

use Yii;
use thyseus\favorites\models\Favorite;
use thyseus\favorites\models\FavoriteSearch;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * FavoritesController implements the CRUD actions for Favorites model.
 */
class FavoritesController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'create', 'delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Favorites of the currently logged in user.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FavoriteSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Favorite model.
     * @param string $id
     * @param string $language
     * @return mixed
     */
    public function actionView($id)
    {
        $favorite = $this->findModel($id);

        if (Yii::$app->user->id != $favorite->created_by)
            throw new ForbiddenHttpException;

        if (!$favorite->target)
            throw new NotFoundHttpException(Yii::t('favorites', 'The target of the requested favorite does not exist anymore.'));

        return $this->render('view', [
            'model' => $favorite,
        ]);
    }

    public function guessTargetAttribute($target)
    {
        foreach (['title', 'name', 'number', 'firstname', 'lastname', 'sigil', 'slug', 'identifier', 'description', 'id'] as $guess)
            if ($target->hasAttribute($guess) || method_exists($target, 'get' . ucfirst($guess)))
                return $guess;

        throw new Exception(Yii::t('favorite', 'Could not guess target identifier attribute. Please provide it manually.'));
    }

    public function generateUrl($model, $target_id)
    {
        $x = explode("\\", $model);
        $x = array_pop($x);
        $x = strtolower(preg_replace('/(?<!^)[A-Z]+/', '-$0', $x));
        $x = '/' . $x . '/view';

        return Url::to([$x, 'id' => $target_id], true);
    }

    /**
     * Creates a new Favorites model.
     * @return mixed
     */
    public function actionCreate($model, $target_id, $url = null, $target_attribute = null)
    {
        if (!$url)
            $url = $this->generateUrl($model, $target_id);

        if (!$target_attribute)
            $target_attribute = $this->guessTargetAttribute(new $model);

        $favorite = Yii::createObject([
            'class' => Favorite::className(),
            'model' => $model,
            'target_id' => $target_id,
            'target_attribute' => $target_attribute,
            'created_by' => Yii::$app->user->id,
            'url' => $url,
        ]);

        if ($favorite->save()) {
            Yii::$app->getSession()->setFlash('success', Yii::t(
                'favorites', 'The Favorite has been added'));

            return $this->redirect(['view', 'id' => $favorite->id]);
        } else {
            Yii::$app->getSession()->setFlash('danger', Yii::t(
                'favorites', 'The Favorite could not be added. ') . json_encode($favorite->getErrors()));

            return $this->goBack();
        }
    }

    /**
     * Deletes an existing Favorite model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $favorite = $this->findModel($id);

        if (Yii::$app->user->id == $favorite->created_by)
            $favorite->delete();
        else
            throw new ForbiddenHttpException;

        return $this->redirect(['index']);
    }

    /**
     * Finds the Favorite model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Favorite the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Favorite::findOne(['id' => $id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('favorites', 'The requested favorite does not exist.'));
        }
    }
}
