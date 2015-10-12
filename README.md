poprigun-chat
=============
poprigun-chat

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require poprigun/chat "dev-dev"
```

or add

```
"poprigun/chat": "dev-dev"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php```

<?= \poprigun\chat\widgets\StaticChat::widget([
    'template'  =>  'path',
    'node => true,
    'options' => [
        'dialogTime' => 0,
        'messageTime' => 0,
        'form' => '#poprigun-chat-send-form',
    ],
]);?>

<?= \poprigun\chat\widgets\ChatButton::widget([
        'options' =>    [
            'receiver_id' => $user->id,
            'text'  =>  'Message',
        ],
    ]
)?>

```js```

$(window).ready(function(){
    poprigunChat.loadDialogs();
    poprigunChat.listenServerDialog();
});

Template
--------
Templates build with <a href="http://handlebarsjs.com/" target="_blank">Handlebars</a>

Migration
---------
yii migrate --migrationPath=@vendor/poprigun/chat/migrations

Main config
------------
  'modules' => [
     'poprigun_chat' => [
         'class' => \poprigun\chat\PChatModule::className(),
         'params' => [
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
     ],
 ],
