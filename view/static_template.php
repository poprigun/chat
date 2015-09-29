<div id="poprigun-chat">
    <div class="col-sm-12">
        <div class="col-sm-4">
            <div class="panel panel-primary">
                <div class="panel-heading">Dialogs</div>
                <div class="panel-body">
                    <ul id="poprigun-chat-dialog-block" class="media-list"></ul>
                </div>
            </div>
        </div>

        <div class="col-sm-8">
            <div class="panel panel-primary">
                <div class="panel-heading">Chat
                <div id="poprigun-dialog-delete">X</div>
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
                        ?>

                        <?= $form->field($model,'message')
                            ->textInput([
                                'pleholder' => 'Enter message ...',
                                'class' =>  'poprigun-chat-form-message'
                            ]);
                        ?>

                        <?= $form->field($model,'receiverId',[
                            'options' => [
                                'class' =>  ''
                            ],
                        ])->hiddenInput([
                            'class' =>  'poprigun-chat-receiver-id'
                        ])->label(false)?>

                        <?= $form->field($model,'messageType',[
                            'options' => [
                                'class' =>  ''
                            ],
                        ])->hiddenInput([
                            'value' => \poprigun\chat\models\PoprigunChatMessage::MESSAGE_TO_DIALOG,
                        ])->label(false)?>

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

<div id="poprigun-chat-dialog" class="hide" data-url="<?=$url?>">
    <li class="media poprigun-chat-dialog-id" data-block-dialog_id="data-dialog">
        <div class="media-message">
            <div class="media">
                <a class="pull-left" href="#">
                    <img data-block-image="src" class="media-object img-circle" style="height:40px;" src="">
                </a>
                <div class="media-body">
                    <h5 data-block-user_name="html"></h5>
                    <small data-block-last_message="html" class="text-muted"></small>
                </div>
            </div>
        </div>
    </li>
</div>

<div id="poprigun-chat-message" class="hide">
    <li class="media" data-block-message_id="data-message">
        <div class="media-message">
            <div class="media">
                <a data-block-link="href" class="pull-left" href="#">
                    <img data-block-user_avatar="src" class="media-object chat-image" src="">
                </a>
                <div class="media-body">
                    <div data-block-message="html" class="message-text"></div>
                    <br>
                    <small data-block-user_name="html" class="text-muted"></small>
                    <hr>
                </div>
            </div>
        </div>
    </li>
</div>
