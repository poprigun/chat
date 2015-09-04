<?php

namespace poprigun\chat\controllers;

use poprigun\chat\Chat;
use poprigun\chat\filters\AjaxFilter;
use poprigun\chat\models\PoprigunChat;
use poprigun\chat\models\PoprigunChatDialog;
use Yii;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

class ChatController extends Controller{

    private $user;
    private $chatSettings;

    public function init(){

        $this->user = Yii::$app->user->identity;
    }

    public function behaviors(){

        return [
            'ajaxAccess' => [
                'class' => AjaxFilter::className(),
            ],
        ];
    }

    /**
     * Get user dialogs
     *
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionGetDialogs(){

        $dialogs = PoprigunChatDialog::getUserDialogs($this->user->id);
        $result = self::getDialogsArray($dialogs);

        Yii::$app->response->format = Response::FORMAT_JSON;
        return $result;

    }

    /**
     * Get messages for dialog
     *
     * @param integer $dialogId
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionGetDialogMessages($dialogId){

        /**
         * @var $dialog PoprigunChatDialog
         */
        $dialog = PoprigunChatDialog::findOne(['id' => $dialogId]);
        $offset = Yii::$app->request->get('offset');
        $old = Yii::$app->request->get('old',false);
        $count = Yii::$app->request->get('count',false);

        $options = Yii::$app->request->get('options',false);
        $limit = empty($options['count']) ? Chat::$defaultCount : $options['count'];

        if(null == $dialog || !$dialog->isAllowed($this->user->id)){
            throw new \BadMethodCallException;
        }

        if($old){
            $json = self::getMessageArray($dialog->getOldMessages($limit, $offset),$options);
        }else{

            $json = self::getMessageArray($dialog->getLastMessages(empty($offset) ? $limit : null, $offset),$options);
        }

        if(!empty($json)){
            if($offset == null){
                krsort($json);
                $json = array_values($json);
            }
            if($count){
                $json['count'] = $dialog->getPoprigunChat()->count();
            }
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return $json;
    }

    /**
     * Send message
     *
     * @return bool
     * @throws BadRequestHttpException
     */
    public function actionSendMessage(){

        $id = Yii::$app->request->post('id');
        $type = Yii::$app->request->post('type');
        $message = Yii::$app->request->post('message');

        Yii::$app->response->format = Response::FORMAT_JSON;
        return PoprigunChatDialog::isMessageSend($this->user->id, $id, $type, $message);
    }

    private static function getMessageArray($dialog, $options = []){

        $result = [];
        foreach($dialog as $key => $message){
            $tempArray = [];
            /**
             * @var $message PoprigunChat
             */
            $tempArray['user_id'] = $message->user_id;
            $tempArray['user_name'] = $message->userName;
            $tempArray['date'] = strtotime($message->created_at);
            $tempArray['message'] = $message->message;
            $tempArray['message_id'] = $message->id;
            $tempArray['view'] = $message->view;
            $tempArray['dialog_id'] = $message->dialog_id;

            if(!isset($options['showAvatar']) || $options['showAvatar'] == true){
                $tempArray['user_avatar'] =  $message->userAvatar;
            }
            $result[] = $tempArray;
        }

        return $result;
    }

    private static function getDialogsArray($dialogs, $options = []){

        $result = [];
        if(!empty($dialogs)){

            foreach($dialogs as $key => $dialog){
                /**
                 * @var $dialog PoprigunChatDialog
                 */
                $result[$key]['dialog_id'] = $dialog->id;
                $result[$key]['user_id'] = $dialog->user_id;
                $result[$key]['user_name'] = $dialog->interlocutorUserName;
                $result[$key]['group'] = $dialog->group;
                $result[$key]['new_count'] = $dialog->newCount;

                if(!isset($options['showAvatar']) || $options['showAvatar'] == true){
                    $result[$key]['image'] = $dialog->interlocutorImage;
                }

                if(!isset($options['showLastMessage']) || $options['showLastMessage'] == true){
                    $lastMessages = $dialog->lastMessages;
                    $result[$key]['last_message'] =  !empty($lastMessages[0]->message) ? $lastMessages[0]->message : '';
                }
            }
        }

        return $result;
    }

}
