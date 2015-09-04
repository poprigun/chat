<?php

namespace poprigun\chat\models;

use poprigun\chat\interfaces\StatusInterface;
use Yii;

/**
 * This is the model class for table "poprigun_chat_attachment".
 *
 * @property integer $id
 * @property integer $message_id
 * @property string $attachment
 * @property string $updated_at
 * @property string $created_at
 *
 * @property PoprigunChat $message
 */
class PoprigunChatAttachment extends ActiveRecord implements StatusInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'poprigun_chat_attachment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message_id'], 'required'],
            [['message_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['attachment'], 'string', 'max' => 500],
            [['message_id'], 'exist', 'skipOnError' => false, 'targetClass' => PoprigunChat::className(), 'targetAttribute' => ['message_id' => 'id']],
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
            'attachment' => 'Attachment',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessage()
    {
        return $this->hasOne(PoprigunChat::className(), ['id' => 'message_id']);
    }
}
