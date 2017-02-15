# Yii2-favorites

A General Favorites Manager for the Yii 2 framework.
Every ActiveRecord Model can be bookmarked and accessed later by an authorized user.
Contains only one table 'favorites' where everything is stored.

## Installation

```bash
$ composer require thyseus/yii2-favorites
$ php yii migrate/up --migrationPath=@vendor/thyseus/yii2-favorites/migrations
```

## Configuration

Add following lines to your main configuration file:

```php
'modules' => [
    'favorites' => [
        'class' => 'thyseus\favorites\Module',
    ],
],
```

## Integration in your application:

use

```php
use thyseus\favorites\models\Favorite;

$model = CurrentModel::findOne(57);
echo $this->render('@vendor/thyseus/yii2-favorites/views/favorites/_button', [
    'model' => CurrentModel::className(),
    'target' => $model->slug
]);
```

to display a "Set as Favorite" / "Remove Favorite" toggle Button in the view files
of the models you want to make your users to be able to add favorites to.

Note that you can shorten the call if you set an alias like this in your application configuration:

```php
'aliases' => [
    '@favorites' => '@app/vendor/thyseus/yii2-favorites'
],

```

And call the view like:

```php
            echo $this->render('@favorites/views/favorites/_button', [
```

If the model is not identified by the column 'id' by default, for example if you are 
using slugs, you can define the indentifierAttribute inside the model like this:

```php
public function identifierAttribute()
{
    return 'slug';
}
```

If the automatic URL creation if yii2-favorites fails, you can append the URL manually:

```php
use thyseus\favorites\models\Favorite;

$model = CurrentModel::findOne(57);
echo $this->render('@vendor/thyseus/yii2-favorites/views/favorites/_button', [
    'model' => CurrentModel::className(),
    'target' => $model->id,
    'url' => Url::to(['fancy-url', 'id' => 1337]) ,
]);
```

Use the fourth parameter to set an custom target_attribute:

```php
use thyseus\favorites\models\Favorite;

$model = CurrentModel::findOne(57);

echo $this->render('@vendor/thyseus/yii2-favorites/views/favorites/_button', [
    'model' => CurrentModel::className(),
    'target' => $model->id,
    'target_attribute' => 'i-am-referenced-by-this-column',
]);
```

If you want to use composite label identifiers, you can do it like this:

```php
public function getName()
{
    return $this->firstname . ' ' . $this->lastname;
}
```

You can use this code example to make an dynamic menu containing your favorites in the NavBar:

```php
echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'encodeLabels' => false,
    'items' => [
        ['label' => '<span class="glyphicon glyphicon-bookmark"></span>', 'options' => ['class' => 'favorites-menu clickable', 'style' => 'cursor: pointer;'], 'url' => false, 'visible' => !$user->isGuest, 'items' => ['' => '']],
      ]
    ]);
```

```js
$('.favorites-menu').click(function() {
    $.getJSON('".Url::to(['//favorites/favorites/json'])."', function (data) {
        dd = $('.favorites-menu').find('.dropdown-menu');
        dd.html('');
        dd.append('<li><a href=\"".Url::to(['//favorites/favorites/index'])."\">Manage favorites</a></li>');
        data.forEach(function(elem) { dd.append('<li><a href=\"' + elem.url + '\">' + elem.title.substring(0, 60) + '</a><li>') });
    }); 
});
```

## Routes

You can use the following routes to access the favorites module:

* list all favorites of the current logged in user: https://your-domain/favorites/favorites/index
* view: https://your-domain/favorites/favorites/view?id=<id>
* update: 'favorites/update/<id>' => 'favorites/favorites/update',
* delete: 'favorites/delete/<id>' => 'favorites/favorites/delete',
* view: 'favorites/<id>' => 'favorites/favorites/view',

## License

Yii2-favorites is released under the GPLv3 License.
