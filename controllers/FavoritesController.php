<?php

namespace thyseus\favorites\controllers;

use thyseus\favorites\models\Favorite;
use thyseus\favorites\models\FavoriteSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

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
                        'actions' => ['index', 'view', 'create', 'delete', 'json'],
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create' => ['POST'],
                    'delete' => ['POST'],
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

    /**
     * Set an entity as favorite for the currently logged in user.
     * Should be called by AJAX (see favorites/_button.php)
     * @return mixed
     */
    public function actionCreate()
    {
        if (!isset($_POST['model']) && !isset($_POST['target-id']))
            throw new BadRequestHttpException;

        $model = $_POST['model'];
        $target_id = $_POST['target-id'];

        $url = isset($_POST['url']) ? $_POST['url'] : $this->generateUrl($model, $target_id);
        $target_attribute = isset($_POST['target_attribute']) ? $_POST['target_attribute'] : $this->guessTargetAttribute(new $model);

        $favorite = Yii::createObject([
            'class' => Favorite::className(),
            'model' => $model,
            'target_id' => $target_id,
            'target_attribute' => $target_attribute,
            'created_by' => Yii::$app->user->id,
            'url' => $url,
        ]);

        if ($favorite->save()) {
            $this->layout = false;
            return $this->render('_button', [
                'model' => $favorite->model,
                'target' => $favorite->target_id,
            ]);
        }
    }

    public function generateUrl($model, $target_id)
    {
        $x = explode("\\", $model);
        $x = array_pop($x);
        $x = strtolower(preg_replace('/(?<!^)[A-Z]+/', '-$0', $x));
        $x = '/' . $x . '/view';

        return Url::to([$x, 'id' => $target_id], true);
    }

    public function guessTargetAttribute($target)
    {
        foreach (['title', 'name', 'number', 'firstname', 'lastname', 'sigil', 'slug', 'identifier', 'description', 'id'] as $guess)
            if ($target->hasAttribute($guess) || method_exists($target, 'get' . ucfirst($guess)))
                return $guess;

        throw new Exception(Yii::t('favorite', 'Could not guess target identifier attribute. Please provide it manually.'));
    }

    public function actionJson()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $favorites = [];

        foreach(Favorite::find()->where(['created_by' => Yii::$app->user->id])->all() as $favorite)
            if($favorite->target && $favorite->url)
                $favorites[] = ['title' => $favorite->target->{$favorite->target_attribute}, 'url' => $favorite->url];

        return $favorites;
    }

    /**
     * Deletes an existing Favorite model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id = null)
    {
        if (!$id && isset($_POST['id']))
            $id = $_POST['id'];

        $favorite = $this->findModel($id);

        if (Yii::$app->user->id == $favorite->created_by)
            $favorite->delete();
        else
            throw new ForbiddenHttpException;

        $this->layout = false;

        return $this->render('_button', [
            'model' => $favorite->model,
            'target' => $favorite->target_id,
        ]);
    }
}
