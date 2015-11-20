<?php

namespace poprigun\chat\widgets;

use poprigun\chat\ChatAssets;
use poprigun\chat\ChatWithoutNodeAssets;
use poprigun\chat\models\forms\MessageForm;
use poprigun\chat\models\PoprigunChatDialog;
use poprigun\chat\models\PoprigunChatMessage;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

class Chat extends Widget{

    public static $defaultUserAvatar = '/img/avatar.png';
    public static $defaultUserName = 'Annonimus';
    public static $defaultCount = 10;

    public $template;
    public $templateDialog = '@vendor/poprigun/chat/view/template/dialog';
    public $templateMessage = '@vendor/poprigun/chat/view/template/message';

    public $node = false;
    public $settings = [];

    public function init(){

        parent::init();
        $this->registerAssets();
    }

    public function registerAssets(){

        $view = $this->getView();

        $this->settings['count'] = isset($this->count) ? $this->count : self::$defaultCount;
        $this->settings['userId'] = isset($this->settings['userId']) ? $this->settings['userId'] : Yii::$app->user->id;

        $this->settings['templateDialog'] = isset($this->settings['templateDialog']) ?  $this->settings['templateDialog'] : $this->templateDialog;
        $this->settings['templateMessage'] = isset($this->settings['templateMessage']) ?  $this->settings['templateMessage'] : $this->templateMessage;

        if($this->node){
            $this->settings['rooms'] = self::generateRoomIds(Yii::$app->user->id,PoprigunChatDialog::getUserDialogs(Yii::$app->user->id));
            $this->settings['socketUrl'] = isset($this->settings['socketUrl']) ?  $this->settings['socketUrl'] : 'http://'.$_SERVER['SERVER_ADDR'].':8080';
            ChatAssets::register($view);
        }else{
            ChatWithoutNodeAssets::register($view);
        }

        $script = '
             poprigunChat = new PoprigunChat('.Json::encode($this->settings).');
        ';

        $view->registerJs($script,View::POS_END);

        echo $this->renderFile($this->template,[
            'model' =>  new MessageForm(['scenario' => 'to_dialog']),
        ]);
    }

    /**
     * Generate rooms array
     *
     * @param $userId
     * @param $dialogs
     * @return mixed
     */
    public static function generateRoomIds($userId, $dialogs){
        $dIds = ArrayHelper::map($dialogs,'id','id');
        $rooms[$userId] = [];
        foreach($dIds as $id){
            $rooms[$userId][] = $id;
        }

        return $rooms;
    }
}
