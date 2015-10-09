<?php

namespace poprigun\chat\widgets;

use poprigun\chat\ChatAssets;
use poprigun\chat\models\PoprigunChatDialog;
use poprigun\chat\models\PoprigunChatMessage;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
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
    public $count;
    public $socketUrl;

    public function init(){

        parent::init();
        $this->initOptions();
        $this->registerAssets();
    }

    public function initOptions(){

        $this->options['count'] = isset($this->count) ? $this->count : self::$defaultCount;
        $this->options['userId'] = self::codeUserId(Yii::$app->user->id);
        $this->options['socketUrl'] = isset($this->socketUrl) ? $this->count : 'http://'.$_SERVER['SERVER_ADDR'].':8080';
    }

    public function registerAssets(){

        $view = $this->getView();
        ChatAssets::register($view);

        echo $this->renderFile($this->template,[
            'model' => new PoprigunChatMessage(),
            'options' => $this->options,
            'rooms'   =>  self::generateRoomIds(Yii::$app->user->id,PoprigunChatDialog::getUserDialogs(Yii::$app->user->id)),
        ]);
    }
    // Code user id
    public static function codeUserId($userId){
        return ('userId'.$userId);
    }
    // Decode user id
    public static function decodeUserId($code){
        return str_replace('userId','',$code);
    }
    // Code dialog id
    public static function codeDialogId($dialogId){
        return ('dialogId'.$dialogId);
    }
    // Decode dialog id
    public static function deccodeDialogId($code){
        return str_replace('dialogId','',$code);
    }
    // Generate rooms array
    public static function generateRoomIds($userId, $dialogs){
        $dIds = ArrayHelper::map($dialogs,'id','id');
        $userId = self::codeUserId($userId);
        $rooms[$userId] = [];
        foreach($dIds as $id){
            $rooms[$userId][] = self::codeDialogId($id);
        }

        return $rooms;
    }
}
