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
     * Get dialog messages
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMessages(){

        return $this->hasMany(PoprigunChatMessage::className(), ['dialog_id' => 'id']);
    }

    /**
     * Get dialog users
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDialogUsers(){

        return $this->hasMany(PoprigunChatUser::className(), ['dialog_id' => 'id']);
    }

    /**
     * Check if dialog is exist
     *
     * @param integer $senderId
     * @param integer $dialogId
     * @return bool|PoprigunChatDialog
     */
    public static function getDialog($senderId, $dialogId){

        return PoprigunChatDialog::find()
            ->innerJoinWith('dialogUsers')
            ->andWhere([
                PoprigunChatDialog::tableName().'.id'   => $dialogId,
                PoprigunChatUser::tableName().'.user_id'=> $senderId,
            ])->one();
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

        $dialog = self::find()
            ->innerJoinWith([
                'dialogUsers' => function ($q) {
                    $q->from('poprigun_chat_user pcu1');
                },
            ])
            ->innerJoinWith([
                'dialogUsers' => function ($q) {
                    $q->from('poprigun_chat_user pcu2');
                },
            ])
            ->andWhere(['pcu1.user_id' => $senderId])
            ->andWhere(['pcu2.user_id' => $receiverId])
            ->andWhere([PoprigunChatDialog::tableName().'.type' => $type])
            ->andWhere([
                'or',
                'pcu1.status' => PoprigunChatUser::STATUS_ACTIVE,
                'pcu2.status' => PoprigunChatUser::STATUS_ACTIVE,
            ])
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
        if(!$dialog->save()){
            throw new \BadMethodCallException('Dialog doesn`t created');
        }

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
        return $messageUser->save();
    }

    /**
     * Get message count current dialog
     *
     * @param int $view
     * @param null $userId
     * @return int|string
     */
    public function getMessageCount($view = PoprigunChatUserRel::NEW_MESSAGE, $userId = null){

        $userId = $userId === null ? \Yii::$app->user->id : $userId;

        return PoprigunChatUserRel::find()
            ->innerJoinWith('dialogUser')
            ->innerJoinWith('dialogUser.dialog')
            ->andWhere([self::tableName().'.id' => $this->id])
            ->andWhere([PoprigunChatUser::tableName().'.user_id' => $userId])
            ->andWhere([PoprigunChatUserRel::tableName().'.is_view' => $view])
            ->count();
    }

    /**
     * Get message count all dialog
     *
     * @param int $view
     * @param null $userId
     * @return int|string
     */
    public static function getUserMessageCount($view = PoprigunChatUserRel::NEW_MESSAGE, $userId = null){

        $userId = $userId === null ? \Yii::$app->user->id : $userId;

        return PoprigunChatUserRel::find()
            ->innerJoinWith('dialogUser')
            ->andWhere([PoprigunChatUser::tableName().'.user_id' => $userId])
            ->andWhere([PoprigunChatUserRel::tableName().'.is_view' => $view])
            ->count();
    }

    /**
     * Send message
     *
     * @param integer $senderId
     * @param string $text
     * @param array $receivers
     * @return bool|Message
     */
    public function sendMessage($senderId, $text, array $receivers = []){

        $message = new PoprigunChatMessage();
        $message->message = $text;
        $message->dialog_id = $this->id;
        $message->author_id = $senderId;
        if(!$message->save()){
            return false;
        }

        array_push($receivers,$senderId);

        $receivers = PoprigunChatUser::find()->andWhere(['user_id' => $receivers])->asArray()->all();
        foreach($receivers as $receiver){
            $rel = new PoprigunChatUserRel();
            $rel->message_id = $message->id;
            $rel->chat_user_id = $receiver['id'];
            $rel->is_view = $receiver['user_id'] == $senderId ? PoprigunChatUserRel::OLD_MESSAGE : PoprigunChatUserRel::NEW_MESSAGE;
            $rel->status = PoprigunChatUserRel::STATUS_ACTIVE;
            $rel->save();
        }

        return $message;
    }

    /**
     * Change status all dialog message for user
     *
     * @param null $userId
     * @return bool|int
     */
    public function setDialogStatus($status = PoprigunChatUserRel::STATUS_ACTIVE, $userId = null){

        $userId = $userId ? $userId : Yii::$app->user->id;

        $messageIds = PoprigunChatMessage::find()
            ->select([PoprigunChatMessage::tableName().'.id', PoprigunChatUser::tableName().'.id as chat_user_id'])
            ->innerJoinWith('messageUserRel')
            ->innerJoinWith('messageUserRel.dialogUser')
            ->andWhere([PoprigunChatMessage::tableName().'.dialog_id' => $this->id])
            ->andWhere([PoprigunChatUser::tableName().'.user_id' => $userId])
            ->asArray()
            ->all();

        if(empty($messageIds)){
            return false;
        }

        return PoprigunChatUserRel::updateAll(['status' => $status],[
            'message_id' => ArrayHelper::map($messageIds,'id','id'),
            'chat_user_id' => ArrayHelper::map($messageIds,'chat_user_id','chat_user_id'),
        ]);
    }

    /**
     * Change status all dialog message for user
     *
     * @param null $userId
     * @return bool|int
     */
    public static function setStaticDialogStatus($dialogId, $status = PoprigunChatUserRel::STATUS_ACTIVE, $userId = null){
        $dialog = self::findOne(['id' => $dialogId]);

        if(null === $dialog){
            return false;
        }

        return $dialog->setDialogStatus($status,$userId);
    }

    /**
     * Change view status all dialog message for user
     *
     * @param null $userId
     * @return bool|int
     */
    public function setDialogView($view = PoprigunChatUserRel::OLD_MESSAGE, $userId = null){

        $userId = $userId ? $userId : Yii::$app->user->id;

        $messageIds = PoprigunChatMessage::find()
            ->select([PoprigunChatMessage::tableName().'.id', PoprigunChatUser::tableName().'.id as chat_user_id'])
            ->innerJoinWith('messageUserRel')
            ->innerJoinWith('messageUserRel.dialogUser')
            ->andWhere([PoprigunChatMessage::tableName().'.dialog_id' => $this->id])
            ->andWhere([PoprigunChatUser::tableName().'.user_id' => $userId])
            ->asArray()
            ->all();

        if(empty($messageIds)){
            return false;
        }

        return PoprigunChatUserRel::updateAll(['is_view' => $view],[
            'message_id' => ArrayHelper::map($messageIds,'id','id'),
            'chat_user_id' => ArrayHelper::map($messageIds,'chat_user_id','chat_user_id'),
        ]);
    }

    /**
     * Change view status all dialog message for user
     *
     * @param null $userId
     * @return bool|int
     */
    public static function setStaticDialogView($dialogId, $status = PoprigunChatUserRel::OLD_MESSAGE, $userId = null){
        $dialog = self::findOne(['id' => $dialogId]);

        if(null === $dialog){
            return false;
        }

        return $dialog->setDialogView($status,$userId);
    }

    /**
     * Get all user dialogs
     *
     * @param integer $userId
     * @param integer $status
     * @param integer|null $type
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getUserDialogs($userId, $type = null, $status = PoprigunChatUserRel::STATUS_ACTIVE){

        $query = self::find()
            ->innerJoinWith('dialogUsers')
            ->innerJoinWith('messages')
            ->innerJoinWith('messages.messageUserRel')
            ->andWhere([PoprigunChatUser::tableName().'.user_id' => $userId])
            ->andWhere([PoprigunChatUserRel::tableName().'.status' => $status])
            ->andFilterWhere(['type' => $type]);

        return $query;
    }
}
