<?php

namespace poprigun\chat;

use yii\web\AssetBundle;

class ChatWithoutNodeAssets extends AssetBundle{

    public $sourcePath = '@vendor/poprigun/chat/assets';

    public $css = [
        'chat.css'
    ];
    public $js = [
        'handlebars.min-v4.0.3.js',
        'withoutNode.js',
    ];
    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\YiiAsset',
    ];

}