<?php

namespace poprigun\chat\models;

class ActiveRecord extends \yii\db\ActiveRecord{

    const DATE_FORMAT = "Y-m-d H:i:s";

    public function beforeSave($insert){

        $date =  new \DateTime('now', new \DateTimeZone('UTC'));

        if($this->isNewRecord)
            $this->created_at = $date->format(self::DATE_FORMAT);
        else
            $this->updated_at = $date->format(self::DATE_FORMAT);

        return parent::beforeSave($insert);
    }
}
