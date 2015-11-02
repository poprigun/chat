<?php

namespace poprigun\chat\controllers;

use poprigun\chat\filters\AjaxFilter;
use poprigun\chat\models\PoprigunChatMessage;
use poprigun\chat\models\PoprigunChatDialog;
use poprigun\chat\models\PoprigunChatUserRel;
use poprigun\chat\widgets\Chat;
use Yii;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;

class ChatController extends Controller{

    public $user;

    public function init(){
        $this->user = Yii::$app->user->identity;
    }

    public function behaviors(){

        return [
            'ajaxAccess' => [
                'class' => AjaxFilter::className(),
            ],
            'responseData' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
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

        $type = Yii::$app->request->get('dialog_type');
        $dialogs = PoprigunChatDialog::getUserDialogs($this->user->id, $type);
        $result = $this->getDialogsArray($dialogs);
        return $result;
    }

    /**
     * Get messages for dialog
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionGetDialogMessages(){

        /**
         * @var $dialog PoprigunChatDialog
         */
        $dialogId = Yii::$app->request->get('dialogId');
        $dialog = PoprigunChatDialog::findOne(['id' => Chat::decodeDialogId($dialogId)]);

        $messageId = Yii::$app->request->get('messageId');
        $old = Yii::$app->request->get('old',false);
        $count = Yii::$app->request->get('count',false);
        $messageCount = Yii::$app->request->get('message_count',false);

        if(null == $dialog || !$dialog->isAllowed($this->user->id)){
            throw new \BadMethodCallException;
        }

        $limit = !$messageCount ? Chat::$defaultCount : $messageCount;

        if($old){
            $messages = $dialog->getMessages($limit, $messageId,true);
        }else{
            $messages = $dialog->getMessages(empty($messageId) ? $limit : null, $messageId);
        }

        $chatUser = $dialog->getPoprigunChatUsers()->andWhere(['user_id' => $this->user->id ])->one();

        PoprigunChatUserRel::setReadMessage($chatUser->id, PoprigunChatUserRel::getUnreadMessage($chatUser->id,$messages));
        $json = $this->getMessageArray($messages);
        if(!empty($json)){
            krsort($json);
            $json = array_values($json);

            if($count){
                $json['count'] = $dialog->getPoprigunChat()->count();
            }
        }

        return $json;
    }

    /**
     * Send message
     *
     * @return bool
     * @throws BadRequestHttpException
     */
    public function actionSendMessage(){

        $model = new PoprigunChatMessage();
        if($model->load(Yii::$app->request->post())){

            $model->author_id = $this->user->id;
            $validate = ActiveForm::validate($model);
            if(empty($validate)){
                $newMessage = $model->sendMessage();
                $result =  $newMessage === false ? $newMessage : $this->getMessageArray([$newMessage]);
            }else{
                $result = $validate;
            }

        }else{
            $result = $model->errors;
        }
        return $result;
    }

    /**
    * Delete dialog (change status)
    *
    * @param $dialogId
    * @return array
    */
    public function actionDeleteDialog($dialogId){
        return [
            'status' => PoprigunChatDialog::deleteDialog(Chat::decodeDialogId($dialogId)) ? 'success':'error'
        ];
    }

    /**
    * Delete message (change status)
    *
    * @param $messageId
    * @return array
    */
    public function actionDeleteMessage($messageId){
        return [
            'status' => PoprigunChatUserRel::deleteMessage(Chat::decodeUserId($messageId)) ? 'success':'error'
        ];
    }

    public function getMessageArray($dialog){

        $result = [];
        foreach($dialog as $key => $message){
            $tempArray = [];
            /**
             * @var $message PoprigunChatMessage
             */

            $tempArray['user_id'] = $message->author_id;
            $tempArray['user_name'] = $message->userName;
            $tempArray['date'] = strtotime($message->created_at);
            $tempArray['message'] = $message->message;
            $tempArray['message_id'] = $message->id;
            $tempArray['dialog_id'] = Chat::codeDialogId($message->dialog_id);
            $tempArray['user_avatar'] =  $message->userAvatar;
            $result[] = $tempArray;
        }

        return $result;
    }

    public function getDialogsArray($dialogs){

        $result = [];
        if(!empty($dialogs)){

            foreach($dialogs as $key => $dialog){
                /**
                 * @var $dialog PoprigunChatDialog
                 */
                $result[$key]['dialog_id'] = Chat::codeDialogId($dialog->id);
                $result[$key]['user_id'] = $dialog->author_id;
                $result[$key]['user_name'] = $dialog->userName;
                $result[$key]['new_count'] = $dialog->newCount;
                $result[$key]['image'] = $dialog->userAvatar;
                $lastMessages = $dialog->getMessages(1);
                $result[$key]['last_message'] =  current(array_values($lastMessages)) ?  current(array_values($lastMessages))->message : '';
            }
        }

        return $result;
    }
}
