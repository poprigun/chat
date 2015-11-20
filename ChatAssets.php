<?php

namespace poprigun\chat;

use yii\web\AssetBundle;

class ChatAssets extends AssetBundle{

    public $sourcePath = '@vendor/poprigun/chat/assets';

    public $css = [
        'chat.css'
    ];
    public $js = [
        '//cdn.socket.io/socket.io-1.3.5.js',
        'mustache.min.js',
        'develop.js',
    ];
    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\YiiAsset',
    ];

}