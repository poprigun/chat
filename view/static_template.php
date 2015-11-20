<div id="poprigun-chat">
    <div class="col-sm-12">
        <div class="col-sm-4">
            <div class="panel panel-primary">
                <div class="panel-heading" style="height: 32px">Dialogs</div>
                <div class="panel-body">
                    <ul id="poprigun-chat-dialog-block" style="height: 400px" class="media-list"></ul>
                </div>
            </div>
        </div>

        <div class="col-sm-8">
            <div class="panel panel-primary">
                <div class="panel-heading" style="height: 32px">
                        <div class="pull-left" >Chat</div>
                        <div class="pull-right" style="cursor:pointer;" onclick="poprigunChat.deleteDialog()">X</div>
                </div>
                <div class="panel-body poprigun-chat-message-ajax">
                    <ul id="poprigun-chat-message-block" class="media-list"></ul>
                    <div id="poprigun-chat-scroll-down"></div>
                </div>
                <div class="panel-footer">
                    <div class="input-group">
                        <?php $form = \yii\widgets\ActiveForm::begin([
                            'id' => 'poprigun-chat-send-form',
                        ]);
                        $form->successCssClass = '';
                        $form->enableClientValidation = true;
                        ?>

                        <?= $form->field($model,'message')
                            ->textInput([
                                'pleholder' => 'Enter message ...',
                                'class' =>  'poprigun-chat-form-message'
                            ]);
                        ?>

                        <?= $form->field($model,'receiver_id',[
                            'options' => [
                                'class' =>  ''
                            ],
                        ])->hiddenInput([
                            'class' =>  'poprigun-chat-receiver-id',
                        ])->label(false)?>

                        <?= $form->field($model,'message_type',[
                            'options' => [
                                'class' =>  ''
                            ],
                        ])->hiddenInput([
                            'value' => \poprigun\chat\models\PoprigunChatMessage::MESSAGE_TO_DIALOG,
                        ])->label(false)?>

                        <?= $form->field($model,'dialog_id',[
                            'options' => [
                                'class' =>  'dialog_id'
                            ],
                        ])->hiddenInput()->label(false)?>

                        <span class="input-group-btn">
                            <button class="btn btn-info" type="submit">SEND</button>
                        </span>
                        <?php $form->end()?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
