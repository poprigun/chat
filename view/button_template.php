<?php

$this->registerJs("
    $(document).on('afterValidate submit', '#poprigun-chat-form', function (event, messages) {
        if(event.type == 'afterValidate') {
        console.log(messages)
            if (messages != false) {
                $('#poprigun-chat-message-popup').modal('hide');
            }
        } else {
           var data = $(this).data('yiiActiveForm');
            data.validated = false;
            $(this).yiiActiveForm('resetForm');
            return false;
        }
    });

    $('.poprigun-message-button').on('click',function(){
        $('.poprigun-chat-message-popup').modal();
    });
");
?>

<button type="button" class="poprigun-message-button"  data-toggle="modal" data-target="#poprigun-chat-message-popup"><?=$settings['text']?></button>

<?php \yii\bootstrap\Modal::begin([
    'id'    =>  'poprigun-chat-message-popup',
    'header' => '<b>' . Yii::t('app', 'Write new message') . '</b>',
])?>

    <?php $form = \yii\widgets\ActiveForm::begin([
        'id'        =>  'poprigun-chat-form',
        'action'    =>  \yii\helpers\Url::to(['poprigun_chat/chat/send-message']),
        'enableAjaxValidation'  =>  true,
        'validateOnChange' => false,
        'validateOnBlur' => false,
    ])?>

    <?= $form->field($model,'message')
        ->textarea(['pleholder' => 'Write message ...']);
    ?>

    <?= $form->field($model,'receiver_id',[
        'options' => [
            'class' =>  ''
        ],
    ])->hiddenInput()->label(false)?>
    <?= $form->field($model,'message_type',[
        'options' => [
            'class' =>  ''
        ],
    ])->hiddenInput()->label(false)?>

    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-primary">Send</button>

    <?php $form->end()?>

<?php \yii\bootstrap\Modal::end()?>
