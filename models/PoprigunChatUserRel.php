<?php

namespace poprigun\chat\models;

use poprigun\chat\interfaces\StatusInterface;
use Yii;

/**
 * This is the model class for table "poprigun_chat_user_rel".
 *
 * @property integer $id
 * @property integer $message_id
 * @property integer $chatUserId
 * @property integer $status
 * @property integer $is_view
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
            [['message_id', 'chat_user_id'], 'required'],
            [['message_id', 'chat_user_id', 'is_view', 'status'], 'integer'],
            [['chat_user_id'], 'exist', 'skipOnError' => false, 'targetClass' => PoprigunChatUser::className(), 'targetAttribute' => ['chat_user_id' => 'id']],
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
            'chat_user_id' => 'Chat User ID',
            'is_view' => 'Is View',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChatUser(){
        return $this->hasOne(PoprigunChatUser::className(), ['id' => 'chat_user_id']);
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

        $message = PoprigunChatMessage::find()
            ->innerJoinWith('dialog')
            ->innerJoinWith('dialog.poprigunChatUsers')
            ->andWhere([PoprigunChatMessage::tableName().'.id' => $messageId])
            ->andWhere([PoprigunChatUser::tableName().'.user_id' => $userId])
            ->asArray()
            ->one();
        if(empty($message)){
            return false;
        }

        return self::updateAll(['status' => self::STATUS_DELETED],['message_id' => $message['id']]);
    }

    /**
     * Set messages status viewed
     *
     * @param int $chatUserId
     * @param int|array $messageId
     * @return int
     */
    public static function setReadMessage($chatUserId, $messageId){

        return self::updateAll(['is_view' => self::OLD_MESSAGE],['message_id' => $messageId, 'chat_user_id' => $chatUserId]);
    }

    /**
     * Get unread message
     *
     * @param int $chatUserId
     * @param array $messages
     * @return array
     */
    public static function getUnreadMessage($chatUserId, $messages){
        $result = [];

        if(!empty($messages)){
            foreach($messages as $key => $message){
                foreach($message->chatUserRel as $messageRel){
                    if($messageRel->is_view == self::NEW_MESSAGE && $chatUserId == $messageRel->chat_user_id){
                        $result[$key] = $message->id;
                    }
                }
            }
        }
        return $result;
    }
}