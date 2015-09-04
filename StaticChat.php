<?php

namespace poprigun\chat;

use Yii;

class StaticChat extends Chat{
    /**
     * @var string template path
     */
    public $template = '@vendor/poprigun/chat/view/static_template.php';

    public function init(){

        parent::init();
    }

}
