poprigun-chat
=============
poprigun-chat

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist poprigun/chat "*"
```

or add

```
"poprigun/chat": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= \poprigun\chat\StaticChat::widget([]);?>```

Migration
---------
yii migrate --migrationPath=@vendor/poprigun/chat/migrations

Main config
------------
 'controllerMap' => [
        'pchat' => 'poprigun\chat\controllers\ChatController',
        'pchat-settings' => [
            'userModel' => \frontend\models\User::className(),
            'userAvatarMethod' => [
                'class' =>\frontend\models\User::className(),
                'method' =>'avatar',
            ],
            'userNameMethod' => [
                'class' =>\frontend\models\Profile::className(),
                'method' =>'fullName',
                'relation' => 'profile',
            ],
        ],
    ],
