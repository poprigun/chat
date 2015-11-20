<?php
/**
 * User: poprigun
 */

namespace poprigun\chat\models;

use yii\base\Model;

class SearchMessage extends Model{

    public $dialogId = null;
    public $limit = null;
    public $userId;
    public $startId = null;
    public $oldMessages = false;
    public $view = [
        PoprigunChatUserRel::NEW_MESSAGE,
        PoprigunChatUserRel::OLD_MESSAGE,
    ];
    public $status = PoprigunChatMessage::STATUS_ACTIVE;
    public $sort = SORT_DESC;

    public function init() {
        $this->userId = $this->userId ? $this->userId : \Yii::$app->user->id;
    }

    public function search($query){
        $searchMessage = $query
            ->innerJoinWith('messageUserRel.dialogUser')
            ->where([PoprigunChatMessage::tableName().'.dialog_id' => $this->dialogId])
            ->andWhere([PoprigunChatUserRel::tableName().'.is_view' => $this->view])
            ->andWhere([PoprigunChatUserRel::tableName().'.status' => $this->status])
            ->andWhere([PoprigunChatUser::tableName().'.user_id' => $this->userId])
            ->orderBy([PoprigunChatMessage::tableName().'.id' => $this->sort]);


        if($this->startId){
            if($this->oldMessages){
                $query->andWhere(['<',PoprigunChatMessage::tableName().'.id',$this->startId]);
            }else{
                $query->andWhere(['>',PoprigunChatMessage::tableName().'.id',$this->startId]);
            }

        }
        if(null != $this->limit){
            $query->limit($this->limit);
        }

        return $searchMessage;
    }
}
