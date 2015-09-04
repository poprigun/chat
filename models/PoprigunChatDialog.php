<?php

namespace poprigun\chat\models;

use poprigun\chat\Chat;
use poprigun\chat\interfaces\StatusInterface;
use Yii;
use yii\base\Exception;

/**
 * This is the model class for table "poprigun_chat_dialog".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $title
 * @property integer $group
 * @property integer $status
 * @property string $updated_at
 * @property string $created_at
 *
 * @property PoprigunChat[] $poprigunChat
 * @property User $user
 * @property PoprigunChatUser[] $PoprigunChatUsers
 */
class PoprigunChatDialog extends ActiveRecord implements StatusInterface
{

    CONST GROUP_TRUE = 1;
    CONST GROUP_FALSE = 0;

    /**
     * @inheritdoc
     */
    public static function tableName(){

        return 'poprigun_chat_dialog';
    }

    /**
     * @inheritdoc
     */
    public function rules(){

        return [
            [['user_id'], 'required'],
            [['user_id', 'status','group'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 128],
            [['user_id'], 'exist', 'skipOnError' => false, 'targetClass' => $this->pchatSettings['userModel'], 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(){

        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'title' => 'Title',
            'group' => 'Group',
            'status' => 'Status',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPoprigunChat(){

        return $this->hasMany(PoprigunChat::className(), ['dialog_id' => 'id']);
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
    public function getPoprigunChatUsers()
    {
        return $this->hasMany(PoprigunChatUser::className(), ['dialog_id' => 'id']);
    }

    /**
     * Get all user dialogs
     *
     * @param integer $userId
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getUserDialogs($userId){

        return self::find()
            ->innerJoinWith('poprigunChatUsers')
            ->where([PoprigunChatUser::tableName().'.user_id' => $userId])->all();
    }

    /**
     * Create dialog and return object
     *
     * @param integer $senderId
     * @param integer $receiverId
     * @param null|string $title
     * @param bool|false $group
     * @return PoprigunChatDialog
     */
    public static function createDialog($senderId,$receiverId, $title = null, $group = 0){

        $dialog = self::isUserDialogExist($senderId,$receiverId);
        if(!$dialog){
            $dialog = new PoprigunChatDialog();
            $dialog->user_id = $senderId;
            $dialog->group = $group;
            $dialog->title = $title;
            $dialog->save();

            $dialog->setUserToDialog($senderId);
            $dialog->setUserToDialog($receiverId);
        }

        return $dialog;
    }

    /**
     * Add user to dialog
     *
     * @param integer $userId
     */
    public function setUserToDialog($userId){

        $messageUser = new PoprigunChatUser();
        $messageUser->user_id = $userId;
        $messageUser->dialog_id = $this->id;
        $messageUser->save();
    }

    /**
     * Check user permission
     *
     * @param integer $userId
     * @return bool
     */
    public function isAllowed($userId){

        if($this->getPoprigunChatUsers()->where(['user_id' => $userId])->count()){
            return true;
        }
        return false;
    }

    /**
     * Check user permission
     * @param integer $dialogId
     * @param  integer $userId
     * @return bool
     */
    public static function idDialogAllowed($dialogId, $userId){

        $allow = self::find()
            ->innerJoinWith('PoprigunChatUsers')
            ->andWhere([PoprigunChatDialog::tableName().'.user_id' => $userId, PoprigunChatDialog::tableName().'.id' => $dialogId])
            ->count();
        if($allow){
            return true;
        }
        return false;
    }

    /**
     * Check if dialog is exist
     *
     * @param integer $senderId
     * @param integer $dialogId
     * @param integer $status
     * @return bool|PoprigunChatDialog
     */
    public static function isDialogExist($senderId, $dialogId, $status = self::STATUS_ACTIVE){

        $dialog = PoprigunChatDialog::find()
            ->innerJoin(PoprigunChatUser::tableName(),PoprigunChatUser::tableName().'.dialog_id = '.PoprigunChatDialog::tableName().'.id')
            ->where([
                PoprigunChatDialog::tableName().'.id'     => $dialogId,
                PoprigunChatDialog::tableName().'.status' => $status,
                PoprigunChatUser::tableName().'.user_id'=> $senderId,
            ])
            ->one();

        return !empty($dialog) ? $dialog : false;
    }

    /**
     * Check if user dialog is exist
     *
     * @param integer $senderId
     * @param integer $receiverId
     * @param integer $status
     * @return bool|PoprigunChatDialog
     */
    public static function isUserDialogExist($senderId, $receiverId, $status = self::STATUS_ACTIVE){

        $dialog = PoprigunChatDialog::find()
            ->innerJoin(PoprigunChatUser::tableName(),PoprigunChatUser::tableName().'.dialog_id = '.PoprigunChatDialog::tableName().'.id')
            ->where([
                PoprigunChatDialog::tableName().'.user_id' => $senderId,
                PoprigunChatUser::tableName().'.user_id' => $receiverId,
            ])->orWhere([
                PoprigunChatDialog::tableName().'.user_id' => $receiverId,
                PoprigunChatUser::tableName().'.user_id' => $senderId,
            ])->andWhere([
                PoprigunChatDialog::tableName().'.status'=>$status,
            ])->groupBy(PoprigunChatDialog::tableName().'.id')
            ->one();

        return !empty($dialog) ? $dialog : false;
    }

    /**
     * Send message and return result of send
     *
     * @param integer $senderId
     * @param integer $id
     * @param string $type
     * @param string $message
     * @return bool
     */
    public static function isMessageSend($senderId, $id, $type, $message){

        try{
            switch($type){
                case 'user':
                    $dialog = self::createDialog($senderId, $id);
                    break;
                case 'dialog':
                    $dialog = self::isDialogExist($senderId, $id);
                    if(!$dialog){
                        throw new \BadMethodCallException;
                    }
                    break;
                default:
                    throw new \BadMethodCallException;
            }
            /**
             * @var $dialog self
             */
            $result = $dialog->addMessageToDialog($senderId,$message);
        }catch (Exception $e){
            error_log($e->getMessage());
            $result = false;
        }

        return $result;
    }

    /**
     * Add message to dialog
     *
     * @param integer $senderId
     * @param string $message
     * @return bool
     */
    private function addMessageToDialog($senderId,$message){

        if(!$this->isAllowed($senderId)){
            return false;
        }

        $poprigunChat = new PoprigunChat();
        $poprigunChat->dialog_id = $this->id;
        $poprigunChat->message = $message;
        $poprigunChat->user_id = $senderId;

        return $poprigunChat->save();
    }

    /**
     * Get last message
     *
     * @param null|int $limit
     * @param null|int $offset
     * @param array|int $view
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getLastMessages($limit = null, $offset = null, $view = [PoprigunChat::NEW_MESSAGE, PoprigunChat::OLD_MESSAGE]){

        $query = $this->hasMany(PoprigunChat::className(), ['dialog_id' => 'id'])
            ->where(['view' => $view])
            ->orderBy(['id' => SORT_DESC]);

        if(null != $limit){
            $query->limit($limit);
        }

        if(null != $offset){
            $query->offset($offset)
                ->orderBy(['id' => SORT_ASC]);
        }

        return $query->all();
    }

    /**
     * Get old|archive messages
     *
     * @param null $limit
     * @param null $offset
     * @param array $view
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getOldMessages($limit = null, $offset = null, $view = [PoprigunChat::NEW_MESSAGE, PoprigunChat::OLD_MESSAGE]){

        $query = $this->hasMany(PoprigunChat::className(), ['dialog_id' => 'id'])
            ->where(['view' => $view])
            ->orderBy(['id' => SORT_DESC]);

        if(null != $limit){
            $query->limit($limit);
        }

        if(null != $offset){
            $query->offset($offset);
        }

        return $query->all();
    }

    /**
     * Get new message count
     *
     * @return int|string
     */
    public function getNewCount(){

        return $this->hasMany(PoprigunChat::className(), ['dialog_id' => 'id'])
            ->where(['view' => PoprigunChat::tableName()])
            ->count();
    }

    /**
     * Get dialog image
     *
     * @return array
     */
    public function getInterlocutorImage(){

        $users = $this->poprigunChatUsers;

        if(empty($users)){
            throw new \BadMethodCallException;
        }

        $image = [];
        $userAvatar = Yii::$app->getSession()->get(Chat::getSessionName()).Chat::$defaultUserAvatar;
        $avatarMethod = $this->pchatSettings['userAvatarMethod'];
        foreach($users as $user){
            if($user->user_id != Yii::$app->user->id){

                if($this->pchatSettings['userModel'] == $avatarMethod['class']){
                    $tempAvatar = $this->user->$avatarMethod['method'];
                }else{
                    $tempAvatar = $this->user->$avatarMethod['relation']->$avatarMethod['method'];
                }

                if(!empty($tempAvatar)){
                    $userAvatar = $tempAvatar;
                }

                $image[] = $userAvatar;
            }
        }

        return $image;
    }

    /**
     * Get users names
     *
     * @return array
     */
    public function getInterlocutorUserName(){

        $users = $this->poprigunChatUsers;

        if(empty($users)){
            throw new \BadMethodCallException;
        }

        $name = [];
        $nameMethod = $this->pchatSettings['userNameMethod'];

        foreach($users as $user){
            if($user->user_id != Yii::$app->user->id){

                if($this->pchatSettings['userModel'] == $nameMethod['class']){
                    $name[] = $user->user->$nameMethod['method'];
                }else{
                    $name[] = $user->user->$nameMethod['relation']->$nameMethod['method'];
                }
            }
        }

        return $name;
    }

}
