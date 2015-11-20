<?php

namespace poprigun\chat\helpers;
use yii\data\ActiveDataProvider;

/**
 * User: poprigun
 */
class DialogFormatter {

    /**
     * Get formatted dialog
     *
     * @param $query
     * @return array
     */
    public static function getFormatter($query){

        $provider = self::getDataProvider($query);
        $data = [];

        foreach($provider->getModels() as $key => $dialog){
            $data[$key]['dialog_id'] = $dialog->id;
            $data[$key]['user_id'] = $dialog->author_id;
            $data[$key]['user_name'] = 'Anonim';
            $data[$key]['new_count'] = $dialog->getMessageCount();
            $data[$key]['image'] = MessageFormatter::$defaultImage;;
        }

        return $data;
    }

    /**
     * Get data provider
     *
     * @param ActiveQuery $query
     * @param int $pageSize
     * @return ActiveDataProvider
     */
    public static function getDataProvider($query, $pageSize = 10){

        return new ActiveDataProvider([
            'key' => 'id',
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);
    }
}