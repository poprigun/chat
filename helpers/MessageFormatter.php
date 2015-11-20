<?php
/**
 * User: poprigun
 */

namespace poprigun\chat\helpers;


use poprigun\chat\models\PoprigunChatMessage;
use poprigun\chat\models\PoprigunChatUser;
use poprigun\chat\models\PoprigunChatUserRel;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class MessageFormatter {

    public static $defaultImage = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJQAAACUCAMAAABC4vDmAAAAulBMVEW2Nje3Nje3Nzi4Nzi5Nzi5ODm6ODm7OTq8OTq9Oju+Oju+Ozy/OzzBPD3CPT7DPT7EPj/FPj/GP0DHP0DHQEHIQEHJQEHJQULKQULLQkPMQkPOREXQREXRRUbSRUbSRkfTRkfUR0jWSEnXSEnZSUrZSkvaSkvbSkvbS0zcS0zdTE3eTE3fTU7gTU7hTk/iTk/iT1DjT1DkT1DkUFHlUFHmUVLnUVLoUlPpUlPpU1TqU1TrU1TrVFXsVFVnCX3GAAADFklEQVR42u3abVPaQBDA8YXwFCwIalAEiwhaCQYEYhIvd9//a/VF+8J2NMnt3l460/t/gJ3fDMwkl1tQ/2DgUA7lUA7lUA7lUA7lUA7lUA7lUA7lUA7lUA7lUA7lUB9LotV02GtCszecrqKkflQe3jTgzxo3YV4nah/A5wX7ulAvffi6/ksdqIMPxfkH2yhxDeVdC6uoyIMqeZFF1AKqtrCFkgFUL5BWUHIEOo2kBZSmCaXSRgWgW8COugP97phREWCKWFGiiUI1BScqAFwBI2oH2HZ8qC4a1WVDhYAv5EL1CKgeE2oHlHY8qAsS6oIFJYCW4EBtiKgNB2pCRE04UECNAZWQUYl5VERGReZRD2TUg3nUlIyamkedk1Hn5lF9MqpvHtUmo9rmUUDPPKpHNvXMo4Zk1NA86pKMujSPWpBRC/OoDRm1MY86kVEn8yhBRgnzKHVGNH1TDKglEbXkQMVEVMyBIhzaAfQO7hqoFQm14kG9k1DvPCg1J5jmigmVEVAZFwr1FfZXd4oNJTtIU0fyodCHv0gxopAHraliRUkfYfIlL0qlnrbJSxUzCvEIjBU7Sh00TYirZMQl5LGhQWoclRWUSqq/L3RRCx2oO+R8XNE0xu1yIFcA1pVMa9xw9LJEVn6hFWTKMkqp10EhafCKnkxawDl+/dcaHwlziatK2eNnjx3/MSNNpS91iWj+8XcczCNBHWlo/U0k8WF/iBNhZJpbFHQoh3Ioh/pPUTKL99vtD522232cSS5UGs4wJ9HfLw6zMDWOelvSPi4CAHSXbwZRMjwDM52F0gxKrj0wl7eWBlBhG8zWDqmodAjmG6Yk1BPw9IRHyQlwNZFIVNYHvvoZCpV0gLNOgkAlHvDmJdqolNtU9NnxC5ToAn9doYcagI0GWqgZ2GmmgYrAVlFlVN6yhmrlVVG3YK/biqgYbBZXQ42sokaVUEew27EKamwZNa6ASsF2aTnq3jrqvhzVso5qlaJOYL9TGWpZA2pZhvJrQPklqBzqKC9GnWpBnYpRz7WgnotR32tBfS9GXdWCuvoL9RMbIgXRASnehwAAAABJRU5ErkJggg==';
    /**
     * Get fortmatted messages
     * @param $query
     * @return array
     */
    public static function getFormatter($query){

        $provider = self::getDataProvider($query);
        $data = [];

        foreach($provider->getModels() as $key => $message){
            $data[] = self::getMessageFormat($message);
        }

        return $data;
    }

    /**
     * Format message
     * @param $message
     * @return mixed
     */
    public static function getMessageFormat($message){

        $data['user_id'] = $message->author_id;
        $data['user_name'] = 'Annonimus';
        $data['date'] = strtotime($message->created_at);
        $data['message'] = $message->message;
        $data['message_id'] = $message->id;
        $data['is_view'] = true;
        $data['dialog_id'] = $message->dialog_id;
        $data['user_avatar'] =  self::$defaultImage;
        return $data;
    }

    /**
     * Get unread message ids
     *
     * @param $messages
     * @return array
     */
    public static function getUnreadMessage($messages){

        foreach($messages as $key=>$value){
            if($value['is_view']){
                unset($messages[$key]);
            }
        }
        return ArrayHelper::getColumn($messages,'message_id','message_id');
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