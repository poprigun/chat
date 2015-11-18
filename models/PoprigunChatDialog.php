<?php

namespace poprigun\chat\models;

use poprigun\chat\interfaces\StatusInterface;
use poprigun\chat\widgets\Chat;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "poprigun_chat_dialog".
 *
 * @property integer $id
 * @property integer $author_id
 * @property string $title
 * @property integer $type
 * @property integer $status
 * @property string $updated_at
 * @property string $created_at
 *
 * @property PoprigunChatMessage[] $poprigunChat
 * @property User $user
 * @property PoprigunChatUser[] $PoprigunChatUsers
 */
class PoprigunChatDialog extends ActiveRecord implements StatusInterface{

    const TYPE_PERSONAL = 1;
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
            [['author_id'], 'required'],
            [['author_id', 'status','type'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 128],
            [['author_id'], 'exist', 'skipOnError' => false, 'targetClass' => $this->pchatSettings['userModel'], 'targetAttribute' => ['author_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(){

        return [
            'id' => 'ID',
            'author_id' => 'Author ID',
            'title' => 'Title',
            'type' => 'Type',
            'status' => 'Status',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPoprigunChat(){

        return $this->hasMany(PoprigunChatMessage::className(), ['dialog_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser(){

        return $this->hasOne($this->pchatSettings['userModel'], ['id' => 'author_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPoprigunChatUsers(){

        return $this->hasMany(PoprigunChatUser::className(), ['dialog_id' => 'id']);
    }

    /**
     * Get all user dialogs
     *
     * @param integer $userId
     * @param integer|null $type
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getUserDialogs($userId, $type = null){

        $query = self::find()
            ->innerJoinWith('poprigunChatUsers')
            ->innerJoinWith('poprigunChat')
            ->innerJoinWith('poprigunChat.chatUserRel')
            ->andWhere([PoprigunChatUser::tableName().'.user_id' => $userId])
            ->andWhere([PoprigunChatUserRel::tableName().'.status' => PoprigunChatUserRel::STATUS_ACTIVE]);

        if(null !== $type){
            $query->andWhere(['type' => $type]);
        }

        return $query->all();
    }

    /**
     * Get dialog or create new if not exist
     *
     * @param integer $senderId (id sender user)
     * @param integer $receiverId (id receiver user)
     * @param null|string $title
     * @return PoprigunChatDialog
     */
    public static function getMessageDialog($senderId, $receiverId, $title = null, $type =  self::TYPE_PERSONAL){

        if($senderId == $receiverId)
            throw new \BadFunctionCallException('The same sender and receiver id');

        $dialog = self::isUserDialogExist($senderId,$receiverId,$type);

        if(!$dialog){
            $dialog = self::createDialog($senderId,$receiverId,$title,$type);
        }

        return $dialog;
    }

    /**
     * Is user dialog exist
     *
     * @param integer $senderId (id sender user)
     * @param integer $receiverId (id receiver user)
     * @param integer $type
     * @return PoprigunChatDialog
     */
    public static function isUserDialogExist($senderId, $receiverId, $type = self::TYPE_PERSONAL){

        if($senderId == $receiverId)
            throw new \BadFunctionCallException('The same sender and receiver id');

        $dialog = self::find()
            ->innerJoinWith([
                'poprigunChatUsers' => function ($q) {
                    $q->from('poprigun_chat_user pcu1');
                },
            ])
            ->innerJoinWith([
                'poprigunChatUsers' => function ($q) {
                    $q->from('poprigun_chat_user pcu2');
                },
            ])
            ->andWhere(['pcu1.user_id' => $senderId])
            ->andWhere(['pcu2.user_id' => $receiverId])
            ->andWhere([PoprigunChatDialog::tableName().'.type' => $type])
            ->one();

        return !empty($dialog) ? $dialog : false;
    }

    /**
     * Create dialog
     *
     * @param int $ownerId
     * @param int|array $userId
     * @param null $title
     * @param int $type
     * @return PoprigunChatDialog
     */
    public static function createDialog($ownerId, $userId, $title = null, $type = self::TYPE_PERSONAL){

        $dialog = new PoprigunChatDialog();
        $dialog->author_id = $ownerId;
        $dialog->title = $title;
        $dialog->type = $type;
        $dialog->save();

        $dialog->setUserToDialog($ownerId);
        if(is_array($userId)){
            foreach($userId as $id){
                $dialog->setUserToDialog($id);
            }
        }else{
            $dialog->setUserToDialog($userId);
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

        return $this->getPoprigunChatUsers()->where(['user_id' => $userId])->exists();
    }

    /**
     * Check user permission
     * @param integer $dialogId
     * @param  integer $userId
     * @return bool
     */
    public static function idDialogAllowed($dialogId, $userId){

        return self::find()
            ->innerJoinWith('poprigunChatUsers')
            ->andWhere([PoprigunChatDialog::tableName().'.id' => $dialogId])
            ->andWhere([PoprigunChatUser::tableName().'.user_id' => $userId])
            ->exists();
    }

    /**
     * Check if dialog is exist
     *
     * @param integer $senderId
     * @param integer $dialogId
     * @return bool|PoprigunChatDialog
     */
    public static function getDialog($senderId, $dialogId){

        $dialog = PoprigunChatDialog::find()
            ->innerJoinWith('poprigunChatUsers')
            ->where([
                PoprigunChatDialog::tableName().'.id'     => $dialogId,
                PoprigunChatUser::tableName().'.user_id'=> $senderId,
            ])->one();

        return $dialog;
    }
    /**
     * Get messages
     *
     * @param null $limit
     * @param null $messageId
     * @param null $oldMessage
     * @param array $view
     * @return array|\yii\db\ActiveRecord[]
     *
     */
    public function getMessages($limit = null, $messageId = null, $oldMessage = false, $view = [PoprigunChatUserRel::NEW_MESSAGE, PoprigunChatUserRel::OLD_MESSAGE]){
        $query = PoprigunChatMessage::find()
            ->innerJoinWith('chatUserRel')
            ->innerJoinWith('chatUserRel.chatUser')
            //->innerJoin(PoprigunChatUser::tableName(),[PoprigunChatUser::tableName().'.id' => PoprigunChatUserRel::tableName().'.chat_user_id'])
            ->where([PoprigunChatMessage::tableName().'.dialog_id' => $this->id])
            ->andWhere([PoprigunChatUserRel::tableName().'.is_view' => $view])
            ->andWhere([PoprigunChatUserRel::tableName().'.status' => StatusInterface::STATUS_ACTIVE])
            //->andWhere([PoprigunChatUserRel::tableName().'.chat_user_id' => Yii::$app->user->id])
            ->andWhere([PoprigunChatUser::tableName().'.user_id' => Yii::$app->user->id])
            ->orderBy([PoprigunChatMessage::tableName().'.id' => SORT_DESC]);
        if(null != $limit){
            $query->limit($limit);
        }

        if(null != $messageId){
            if($oldMessage){
                $query->andWhere(['<',PoprigunChatMessage::tableName().'.id',$messageId]);
            }else{
                $query->andWhere(['>',PoprigunChatMessage::tableName().'.id',$messageId]);
            }

        }else{

        }
        return $query->indexBy('id')->all();
    }

    public static function newMessage($senderId, $receiverId){

    }

    /**
     * Add message to dialog
     *
     * @param integer $senderId
     * @param string $message
     * @return bool
     */
    public function addMessageToDialog($senderId, $message){

        if(!$this->isAllowed($senderId)){
            return false;
        }

        $poprigunChat = new PoprigunChatMessage([
            'dialog_id' => $this->id,
            'message' => $message,
            'author_id' => $senderId,
        ]);

        if($poprigunChat->save()){
            foreach($this->poprigunChatUsers as $user){
                $rel = new PoprigunChatUserRel([
                    'message_id' => $poprigunChat->id,
                    'chat_user_id' => $user->id,
                    'is_view' => PoprigunChatUserRel::NEW_MESSAGE,
                    'status' => PoprigunChatUserRel::STATUS_ACTIVE,
                ]);
                if($senderId == $user->user_id){
                    $rel->is_view = PoprigunChatUserRel::OLD_MESSAGE;
                }

                if(!$rel->save()){
                    error_log($rel->errors);
                }
            }
            return $poprigunChat;
        }
        return false;
    }

    /**
     * Get new message count
     * @param null|integer $dialogId
     * @return int|string
     */
    public function getNewCount($dialogId = null){

        $query = $this->hasMany(PoprigunChatMessage::className(), ['dialog_id' => 'id'])
            ->innerJoinWith('chatUserRel')
            ->andWhere([PoprigunChatUserRel::tableName().'.is_view' => PoprigunChatUserRel::NEW_MESSAGE]);
        if(null !== $dialogId){
            $query->andWhere([PoprigunChatDialog::tableName().'.id' => $dialogId]);
        }
        return $query->count();
    }

    /**
     * Delete dialog/delete dialog messages (change status)
     *
     * @param integer $dialogId
     * @param integer|null $userId
     * @return int
     */
    public static function deleteDialog($dialogId, $userId = null){

        $userId = $userId ? $userId : Yii::$app->user->id;

        $messageIds = PoprigunChatMessage::find()
            ->select([PoprigunChatMessage::tableName().'.id', PoprigunChatUser::tableName().'.id as chat_user_id'])
            ->innerJoinWith('chatUserRel')
            ->innerJoinWith('chatUserRel.chatUser')
            ->andWhere([PoprigunChatMessage::tableName().'.dialog_id' => $dialogId])
            ->andWhere([PoprigunChatUser::tableName().'.user_id' => $userId])
            ->asArray()
            ->all();

        if(empty($messageIds)){
            return false;
        }

        return PoprigunChatUserRel::updateAll(['status' => PoprigunChatUserRel::STATUS_DELETED],[
            'message_id' => ArrayHelper::map($messageIds,'id','id'),
            'chat_user_id' => ArrayHelper::map($messageIds,'chat_user_id','chat_user_id'),
        ]);
    }

    /**
     * Get dialog image
     *
     * @return array
     */
    public function getUserAvatar(){

        $users = $this->poprigunChatUsers;

        if(empty($users)){
            throw new \BadMethodCallException;
        }

        $image = [];
        $avatarMethod = $this->pchatSettings['userAvatarMethod'];
        foreach($users as $user){
            if($user->user_id != Yii::$app->user->id){

                if($this->pchatSettings['userModel'] == $avatarMethod['class']){
                    $tempAvatar = $this->user->$avatarMethod['method'];
                }else{
                    $tempAvatar = $this->user->$avatarMethod['relation']->$avatarMethod['method'];
                }

                $image[] = $tempAvatar;
            }
        }

        return $image;
    }

    /**
     * Get users names
     *
     * @return array
     */
    public function getUserName(){

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
