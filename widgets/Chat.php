<?php

namespace poprigun\chat\widgets;

use poprigun\chat\ChatAssets;
use poprigun\chat\models\PoprigunChatMessage;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

class Chat extends Widget{

    private static $sessionName = 'pchat';
    public static $defaultUserAvatar = '/img/avatar.png';
    public static $defaultUserName = 'Annonimus';
    public static $defaultCount = 10;
    /**
     * @var string template path
     */
    public $template;
    /**
     * @var array widget plugin options
     */
    protected $options = [];
    public $showUserAvatar = true;
    public $showLastMessage = true;
    public $count;

    public function init(){

        parent::init();
        $this->initOptions();
        $this->registerAssets();
    }

    public function initOptions(){

        $this->options['count'] = isset($this->count) ? $this->count : self::$defaultCount;
    }

    public function registerAssets(){

        $view = $this->getView();
        ChatAssets::register($view);
        $this->saveWidgetSettings([
            'assetUrl'      =>  ChatAssets::register($view)->baseUrl,
            'showUserAvatar'=>  $this->showUserAvatar,
            'showLastMessage'=>  $this->showLastMessage,
            'message_count'=>  $this->options['count'],
        ]);
        echo $this->renderFile($this->template,[
            'model' => new PoprigunChatMessage(),
            'options' => $this->options,
            'url' => Url::toRoute(['/poprigun_chat/chat/get-dialogs']),
        ]);
    }

    public static function getSessionName(){
        return self::$sessionName;
    }

    private function saveWidgetSettings($settings){
        Yii::$app->session->set(self::$sessionName,$settings);
    }
}
