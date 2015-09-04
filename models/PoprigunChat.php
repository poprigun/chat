<?php

namespace poprigun\chat\models;

use poprigun\chat\Chat;
use poprigun\chat\ChatAssets;
use poprigun\chat\interfaces\StatusInterface;
use Yii;
use yii\web\AssetBundle;

/**
 * This is the model class for table "poprigun_chat".
 *
 * @property integer $id
 * @property integer $dialog_id
 * @property integer $user_id
 * @property string $message
 * @property integer $view
 * @property integer $status
 * @property string $updated_at
 * @property string $created_at
 *
 * @property User $user
 * @property PoprigunChatDialog $dialog
 * @property PoprigunChatAttachment[] $poprigunChatAttachments
 */
class PoprigunChat extends ActiveRecord implements StatusInterface
{

    CONST NEW_MESSAGE = 0;
    CONST OLD_MESSAGE = 1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'poprigun_chat';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dialog_id', 'user_id'], 'required'],
            [['dialog_id', 'user_id', 'view', 'status'], 'integer'],
            [['updated_at', 'created_at'], 'safe'],
            [['message'], 'string', 'max' => 2000],
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
            'view' => 'View',
            'status' => 'Status',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne($this->pchatSettings['userModel'], ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDialog()
    {
        return $this->hasOne(PoprigunChatDialog::className(), ['id' => 'dialog_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPoprigunChatAttachments()
    {
        return $this->hasMany(PoprigunChatAttachment::className(), ['message_id' => 'id']);
    }

    /**
     * Get last dialog messages
     * @param int $limit
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getLastMessages($limit = 1){

        return $this->find()
            ->where(['dialog_id' => $this->dialog_id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    /**
     * Set messages status viewed
     *
     * @param $userId
     * @param $dialogId
     * @param $messageId
     */
    public static function setReadMessage($userId,$dialogId,$messageId){

        if(PoprigunChatDialog::idDialogAllowed($dialogId,$userId)){
            PoprigunChat::updateAll(['view' => self::viewed],['dialog_id' => $dialogId, 'id' => $messageId]);
        }
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

        $userAvatar = Yii::$app->getSession()->get(Chat::getSessionName()).Chat::$defaultUserAvatar;
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
}
