<?php

namespace thyseus\favorites\controllers;

use thyseus\favorites\models\Favorite;
use thyseus\favorites\models\FavoriteSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
            'model_types' => ArrayHelper::map(
                Favorite::find()->where(
                    ['created_by' => Yii::$app->user->id])->select('model')->groupBy('model')->all(), 'model', function ($data) {
                $aliases = Yii::$app->getModule('favorites')->modelAliases;
                return isset($aliases[$data->model]) ? Yii::t('app', $aliases[$data->model]) : $data->model;
            })
        ]);
    }

    /**
     * Displays a Favorite.
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
     *
     * $_POST string|null $label_add label to be displayed when the favorite is going to be added
     * $_POST string|null $label_remove label to be displayed when the favorite is going to be removed
     * $_POST string|null $icon the icon that the favorite should be saved with
     * $_POST string|null $icon_add the icon that the favorite should be displayed with when it is going to be added
     * $_POST string|null $icon_remove the icon that the favorite should be displayed with when it is going to be removed
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionCreate()
    {
        if (!isset($_POST['model']) && !isset($_POST['target-id'])) {
            throw new BadRequestHttpException;
        }

        $post = Yii::$app->request->post();

        $model = $post['model'];
        $target_id = $post['target-id'];
        $icon = $post['icon'] ?? null;

        $url = $_POST['url'] ?? $this->generateUrl($model, $target_id);
        $target_attribute = $_POST['target_attribute'] ?? $this->guessTargetAttribute(new $model);

        $favorite = Yii::createObject([
            'class' => Favorite::className(),
            'model' => $model,
            'target_id' => $target_id,
            'target_attribute' => $target_attribute,
            'created_by' => Yii::$app->user->id,
            'url' => HtmlPurifier::process($url),
            'icon' => $icon,
        ]);

        if ($favorite->save()) {
            $this->layout = false;
            return $this->render('_button', [
                'model' => $favorite->model,
                'target' => $favorite->target_id,
                'label_add' => $post['label_add'] ?? null,
                'label_remove' => $post['label_remove'] ?? null,
                'icon_add' => $post['icon_add'] ?? null,
                'icon_remove' => $post['icon_remove'] ?? null,
                'icon' => $icon,
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
        foreach (['title', 'name', 'number', 'firstname', 'lastname', 'sigil', 'slug', 'identifier', 'description', 'id'] as $guess) {
            if ($target->hasAttribute($guess) || method_exists($target, 'get' . ucfirst($guess))) {
                return $guess;
            }
        }

        throw new Exception(Yii::t('favorite', 'Could not guess target identifier attribute. Please provide it manually.'));
    }

    public function actionJson()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $favorites = [];

        foreach (Favorite::find()->where(['created_by' => Yii::$app->user->id])->all() as $favorite)
            if ($favorite->target && $favorite->url)
                $favorites[] = [
                    'title' => $favorite->targetTitle(),
                    'url' => $favorite->url,
                    'icon' => $favorite->icon,
                ];

        return $favorites;
    }

    /**
     * Deletes an existing Favorite.
     * If deletion is successful, the browser will be redirected to the referrer,
     * most probably coming from the 'favorites/index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id = null)
    {
        if (!$id && isset($_POST['id'])) {
            $id = $_POST['id'];
        }

        $favorite = $this->findModel($id);

        if (Yii::$app->user->id == $favorite->created_by) {
            $favorite->delete();
        } else {
            throw new ForbiddenHttpException;
        }

        return $this->redirect(Yii::$app->request->referrer);
    }
}
