<?php

namespace poprigun\chat\widgets;

use poprigun\chat\models\forms\MessageForm;
use poprigun\chat\models\PoprigunChatMessage;
use Yii;
use yii\base\Widget;

class ChatButton extends Widget{

    public $template = '@vendor/poprigun/chat/view/button_template.php';
    public $settings = [];

    public function init(){

        parent::init();
        $this->initOptions();
        $this->registerAssets();
    }

    public function initOptions(){

        $this->settings['text'] = empty($this->settings['text']) ? 'Message' : $this->settings['text'];
    }

    public function registerAssets(){

        $model =  new MessageForm(['scenario' => 'to_user']);
        $model->receiver_id = $this->settings['receiver_id'];
        $model->message_type = PoprigunChatMessage::MESSAGE_TO_USER;

        echo $this->renderFile($this->template,[
            'model' => $model,
            'settings' => $this->settings,
        ]);
    }

}
