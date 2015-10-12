<?php

namespace poprigun\chat\models;

use poprigun\chat\interfaces\StatusInterface;
use poprigun\chat\widgets\Chat;
use Yii;
/**
 * This is the model class for table "poprigun_chat_message".
 *
 * @property integer $id
 * @property integer $dialog_id
 * @property integer $user_id
 * @property string $message
 * @property integer $messageType
 * @property integer $receiverId
 * @property string $updated_at
 * @property string $created_at
 *
 * @property User $user
 * @property PoprigunChatDialog $dialog
 * @property PoprigunChatAttachment[] $poprigunChatAttachments
 */
class PoprigunChatMessage extends ActiveRecord implements StatusInterface
{

    CONST MESSAGE_TO_USER = 1;
    CONST MESSAGE_TO_DIALOG = 3;

    public $messageType;
    public $receiverId;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'poprigun_chat_message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'message'], 'required'],
            ['dialog_id', 'required', 'on' => 'dialog'],
            ['receiverId', 'valdateReceiver'],
            [['dialog_id', 'user_id', 'receiverId' , 'messageType'], 'integer'],
            [['updated_at', 'created_at'], 'safe'],
            [['message'], 'string', 'skipOnEmpty' => false],
            [['user_id'], 'exist', 'skipOnError' => false, 'targetClass' => $this->pchatSettings['userModel'], 'targetAttribute' => ['user_id' => 'id']],
            [['dialog_id'], 'exist', 'skipOnError' => false, 'targetClass' => PoprigunChatDialog::className(), 'targetAttribute' => ['dialog_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dialog_id' => 'Dialog ID',
            'user_id' => 'User ID',
            'message' => 'Message',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Validate receiver
     *
     * @param $attribute
     * @param $params
     */
    public function valdateReceiver($attribute, $params){

        if($this->messageType == self::MESSAGE_TO_USER){
            $this->receiverId = Chat::decodeUserId($this->receiverId);
        }elseif($this->messageType == self::MESSAGE_TO_DIALOG){
            $this->receiverId = Chat::decodeDialogId($this->receiverId);
        }else{
            $this->addError($attribute,'Incorrect receiver');
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */

    public function getChatUserRel(){
        return $this->hasMany(PoprigunChatUserRel::className(), ['message_id'=>'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser(){
        return $this->hasOne($this->pchatSettings['userModel'], ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDialog(){
        return $this->hasOne(PoprigunChatDialog::className(), ['id' => 'dialog_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPoprigunChatAttachments(){
        return $this->hasMany(PoprigunChatAttachment::className(), ['message_id' => 'id']);
    }

    /**
     * Get user name
     * @return string
     */
    public function getUserName(){

        if(!empty($this->pchatSettings['userNameMethod'])){

            $nameMethod = $this->pchatSettings['userNameMethod'];
            if($this->pchatSettings['userModel'] == $nameMethod['class']){
                $userName = $this->user->$nameMethod['method'];
            }else{
                $userName = $this->user->$nameMethod['relation']->$nameMethod['method'];
            }
        }else{
            $userName = Chat::$defaultUserName;
        }

        return $userName;
    }

    /**
     * Get user avatar
     * @return string
     */
    public function getUserAvatar(){

        $userAvatar = '';
        if(!empty($this->pchatSettings['userAvatarMethod'])){

            $avatarMethod = $this->pchatSettings['userAvatarMethod'];
            if($this->pchatSettings['userModel'] == $avatarMethod['class']){
                $tempAvatar = $this->user->$avatarMethod['method'];
            }else{
                $tempAvatar = $this->user->$avatarMethod['relation']->$avatarMethod['method'];
            }

            if(!empty($tempAvatar)){
                $userAvatar = $tempAvatar;
            }
        }

        return $userAvatar;
    }

    /**
     * Send message
     * @param string|null $title
     * @return bool
     */
    public function sendMessage($title = null){
        try{
            switch($this->messageType){

                case self::MESSAGE_TO_USER:
                    $dialog = PoprigunChatDialog::getMessageDialog($this->user_id, $this->receiverId,$title);
                    break;

                case self::MESSAGE_TO_DIALOG:
                    $dialog = PoprigunChatDialog::getDialog($this->user_id, $this->receiverId);
                    if(null === $dialog){
                        throw new \BadMethodCallException;
                    }
                    break;

                default:
                    throw new \BadMethodCallException;
            }
            $result = $dialog->addMessageToDialog($this->user_id, $this->message);
        }catch (\Exception $e){
            $result = $e->getMessage();
        }

        return $result;
    }
}
