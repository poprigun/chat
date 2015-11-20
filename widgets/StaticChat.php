<?php

namespace poprigun\chat\widgets;

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
