<?php

namespace poprigun\chat\models;

use poprigun\chat\interfaces\StatusInterface;
use Yii;

/**
 * This is the model class for table "poprigun_chat_user".
 *
 * @property integer $id
 * @property integer $dialog_id
 * @property integer $user_id
 * @property string $updated_at
 * @property string $created_at
 *
 * @property User $user
 * @property PoprigunChatDialog $dialog
 */
class PoprigunChatUser extends ActiveRecord implements StatusInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName(){

        return 'poprigun_chat_user';
    }

    /**
     * @inheritdoc
     */
    public function rules(){

        return [
            [['dialog_id', 'user_id'], 'required'],
            [['dialog_id', 'user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => false, 'targetClass' => $this->pchatSettings['userModel'], 'targetAttribute' => ['user_id' => 'id']],
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
            'user_id' => 'User ID',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
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
}
