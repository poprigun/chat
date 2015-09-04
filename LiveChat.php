<?php

namespace poprigun\chat;

use Yii;

class LiveChat extends Chat{

    /**
     * @var string template path
     */
    public $template = '@vendor/poprigun/chat/view/live_template.php';

    public function init(){

        parent::init();
    }
}
