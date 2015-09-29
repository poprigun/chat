<?php

namespace poprigun\chat\widgets;

use poprigun\chat\models\PoprigunChatMessage;
use Yii;
use yii\base\Widget;

class ChatButton extends Widget{

    public $template = '@vendor/poprigun/chat/view/button_template.php';
    public $options = [];

    public function init(){

        parent::init();
        $this->initOptions();
        $this->registerAssets();
    }

    public function initOptions(){

        $this->options['text'] = empty($options['text']) ? 'Message' : $options['text'];
    }

    public function registerAssets(){

        $model =  new PoprigunChatMessage();
        $model->receiverId = $this->options['receiver_id'];
        $model->messageType = PoprigunChatMessage::MESSAGE_TO_USER;

        echo $this->renderFile($this->template,[
            'model' => $model,
            'options' => $this->options,
        ]);
    }

}
