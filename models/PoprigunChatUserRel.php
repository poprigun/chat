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
    public static function tableName(){
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
     * Get message user
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDialogUser(){

        return $this->hasOne(PoprigunChatUser::className(), ['id' => 'chat_user_id']);
    }

    /**
     * Get message
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMessage(){

        return $this->hasOne(PoprigunChatMessage::className(), ['id' => 'message_id']);
    }

    /**
     * Set status
     *
     * @param int $status
     * @return bool|int
     * @throws \Exception
     */
    public function setStatus($status = self::STATUS_ACTIVE){
        $this->status = $status;
        return $this->update();
    }

    /**
     * Set view
     *
     * @param int $view
     * @return bool|int
     * @throws \Exception
     */
    public function setView($view = self::OLD_MESSAGE){
        $this->is_view = $view;
        return $this->update();
    }

}