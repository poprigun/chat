<?php

namespace poprigun\chat\models;

use poprigun\chat\interfaces\StatusInterface;
use Yii;

/**
 * This is the model class for table "poprigun_chat_user_rel".
 *
 * @property integer $id
 * @property integer $message_id
 * @property integer $userId
 * @property integer $status
 * @property integer $view
 *
 * @property PoprigunChatUser $chatUser
 * @property PoprigunChatMessage $chat
 */
class PoprigunChatUserRel extends \yii\db\ActiveRecord implements StatusInterface{

    CONST NEW_MESSAGE = 0;
    CONST OLD_MESSAGE = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'poprigun_chat_user_rel';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message_id', 'user_id'], 'required'],
            [['message_id', 'user_id', 'view', 'status'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => false, 'targetClass' => PoprigunChatUser::className(), 'targetAttribute' => ['user_id' => 'user_id']],
            [['message_id'], 'exist', 'skipOnError' => false, 'targetClass' => PoprigunChatMessage::className(), 'targetAttribute' => ['message_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'message_id' => 'Message ID',
            'user_id' => 'User ID',
            'view' => 'View',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChatUser(){
        return $this->hasOne(PoprigunChatUser::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChat(){
        return $this->hasOne(PoprigunChatMessage::className(), ['id' => 'message_id']);
    }

    /**
     * Delete message (change status)
     *
     * @param integer $messageId
     * @param null $userId
     * @return int
     */
    public static function deleteMessage($messageId, $userId = null){
        $userId = $userId ? $userId : Yii::$app->user->id;
        return self::updateAll(['status' => self::STATUS_DELETED,['message_id' => $messageId,'user_id' => $userId]]);
    }

    /**
     * Set messages status viewed
     *
     * @param int $userId
     * @param int|array $messageId
     * @return int
     */
    public static function setReadMessage($userId, $messageId){
        if(empty($messageId)){
            return false;
        }
        return self::updateAll(['view' => self::OLD_MESSAGE],['user_id' => $userId, 'message_id' => $messageId]);
    }

    public static function getUnreadMessage($userId, $messages){
        $result = [];

        if(!empty($messages)){
            foreach($messages as $key => $message){
                foreach($message->chatUserRel as $messageRel){
                    if($messageRel->view == self::NEW_MESSAGE && $userId == $messageRel->user_id){
                        $result[$key] = $message->id;
                    }
                }
            }
        }
        return $result;
    }
}