<?php

namespace poprigun\chat\models;

use poprigun\chat\interfaces\StatusInterface;
use poprigun\chat\widgets\Chat;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "poprigun_chat_message".
 *
 * @property integer $id
 * @property integer $dialog_id
 * @property integer $author_id
 * @property string $message
 * @property integer $receiverId
 * @property string $updated_at
 * @property string $created_at
 *
 * @property User $user
 * @property PoprigunChatDialog $dialog
 */
class PoprigunChatMessage extends ActiveRecord implements StatusInterface{

    CONST MESSAGE_TO_USER = 1;
    CONST MESSAGE_TO_DIALOG = 3;

    public $receiverId;

    /**
     * @inheritdoc
     */
    public static function tableName(){

        return 'poprigun_chat_message';
    }

    /**
     * @inheritdoc
     */
    public function rules(){

        return [
            [['author_id', 'message'], 'required'],
            ['dialog_id', 'required', 'on' => 'dialog'],
            [['dialog_id', 'author_id', 'receiverId' ], 'integer'],
            [['updated_at', 'created_at'], 'safe'],
            [['message'], 'string', 'skipOnEmpty' => false],
            [['dialog_id'], 'exist', 'skipOnError' => false, 'targetClass' => PoprigunChatDialog::className(), 'targetAttribute' => ['dialog_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(){

        return [
            'id' => 'ID',
            'dialog_id' => 'Dialog ID',
            'author_id' => 'Author ID',
            'message' => 'Message',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Get message user rel
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMessageUserRel(){

        return $this->hasMany(PoprigunChatUserRel::className(), ['message_id'=>'id']);
    }

    /**
     * Get dialog
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDialog(){

        return $this->hasOne(PoprigunChatDialog::className(), ['id' => 'dialog_id']);
    }

    /**
     * Get attachments
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMessageAttachments(){

        return $this->hasMany(PoprigunChatAttachment::className(), ['message_id' => 'id']);
    }

    /**
     * Set message status
     *
     * @param int $status
     * @param null $userId
     * @return bool|int
     */
    public function setMessageStatus($status = PoprigunChatMessage::STATUS_ACTIVE, $userId = null){
        $userId = $userId ? $userId : Yii::$app->user->id;

        $query = $this->getDialog()->getDialogUsers()
            ->select([PoprigunChatMessage::tableName().'.id', PoprigunChatUser::tableName().'.id as chat_user_id'])
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
     * Set message status
     *
     * @param int $messageId
     * @param int $status
     * @param null $userId
     * @return bool|int
     */
    public function setStaticMessageStatus($messageId, $status = PoprigunChatMessage::STATUS_ACTIVE, $userId = null){
        $userId = $userId ? $userId : Yii::$app->user->id;

        $message = self::findOne(['id' => $messageId]);
        if(null === $message){
            return false;
        }

        return $message->setMessageStatus($status,$userId);
    }

    /**
     * Set message(s) view status
     *
     * @param int $messageId
     * @param int $view
     * @param null $userId
     * @return bool|int
     */
    public static function setStaticMessageView($messageId, $view = PoprigunChatUserRel::OLD_MESSAGE, $userId = null){

        $userId = $userId ? $userId : Yii::$app->user->id;

        $messageIds = self::find()
            ->select([self::tableName().'.id', PoprigunChatUser::tableName().'.id as chat_user_id'])
            ->innerJoinWith('messageUserRel')
            ->innerJoinWith('messageUserRel.dialogUser')
            ->andWhere([self::tableName().'.id' => $messageId])
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
}
