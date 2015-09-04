<?php

namespace poprigun\chat;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;

class Chat extends Widget{

    private static $sessionName = 'pchat_asset_url';
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

        $this->options['showUserAvatar'] = $this->showUserAvatar;
        $this->options['showLastMessage '] = $this->showLastMessage ;
        $this->options['count'] = isset($this->count) ? $this->count : self::$defaultCount;
    }

    public function registerAssets(){

        $view = $this->getView();
        ChatAssets::register($view);
        Yii::$app->session->set(self::$sessionName,ChatAssets::register($view)->baseUrl);
        echo $this->renderFile($this->template,['options' => $this->options]);
    }

    public static function getSessionName(){
        return self::$sessionName;
    }
}
