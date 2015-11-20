<?php

namespace poprigun\chat\controllers;

use poprigun\chat\filters\AjaxFilter;
use poprigun\chat\helpers\DialogFormatter;
use poprigun\chat\helpers\MessageFormatter;
use poprigun\chat\models\forms\MessageForm;
use poprigun\chat\models\PoprigunChatMessage;
use poprigun\chat\models\PoprigunChatDialog;
use poprigun\chat\models\PoprigunChatUserRel;
use poprigun\chat\models\SearchMessage;
use poprigun\chat\widgets\Chat;
use Yii;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;

class ChatController extends Controller{

    protected $user;

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
        $dialogQuery = PoprigunChatDialog::getUserDialogs($this->user->id, $type);
        return DialogFormatter::getFormatter($dialogQuery);
    }

    /**
     * Get messages for dialog
     * @return array
     */
    public function actionGetMessages(){

        $dialogId = Yii::$app->request->get('dialogId');
        $messageCount = Yii::$app->request->get('count', Chat::$defaultCount);
        $oldMessage = Yii::$app->request->get('order_by',false);

        $dialog = PoprigunChatDialog::getDialog($this->user->id, $dialogId);

        if(null === $dialog){
            throw new \BadMethodCallException;
        }

        $searchMessage = new SearchMessage([
            'limit' => $messageCount,
         //   'startId' => $messageCount,
            'oldMessages' => $messageCount,
            'dialogId' => $dialogId,
         //   'view' => 1,
        ]);

        $formatter = MessageFormatter::getFormatter($searchMessage->search($dialog->getMessages()));
        $messageIds = MessageFormatter::getUnreadMessage($formatter);
        PoprigunChatMessage::setStaticMessageView($messageIds);

        return $formatter;
    }

    /**
     * Send message
     *
     * @return bool
     * @throws BadRequestHttpException
     */
    public function actionSendMessage(){

        $model = new MessageForm();
        if($model->load(Yii::$app->request->post())){

            $model->sender_id = $this->user->id;
            $validate = ActiveForm::validate($model);

            if(empty($validate)){
                $message = $model->saveMessage();
                $response['response'] =  $message ? 'success' : 'error';
                if(!$message){
                    $response = false;
                }else{
                    $response['data'] = MessageFormatter::getMessageFormat($message);
                }

            }else{
                $response = $validate;
            }
        }else{
            $response = $model->errors;
        }
        return $response;
    }

    /**
    * Delete dialog (change status)
    *
    * @param $dialogId
    * @return array
    */
    public function actionDeleteDialog($dialogId){

        return [
            'status' => PoprigunChatDialog::setStaticDialogStatus($dialogId,PoprigunChatDialog::STATUS_DELETED) ? 'success':'error'
        ];
    }

    /**
     * Delete message (change status)
     *
     * @param integer $messageId
     * @return array
     */
    public function actionDeleteMessage($messageId){

        return [
            'status' =>  PoprigunChatMessage::setStaticMessageStatus($messageId, PoprigunChatMessage::STATUS_DELETED) ? 'success':'error'
        ];
    }
}
