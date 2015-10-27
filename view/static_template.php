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
                        $form->enableClientValidation = false;
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
                            'class' =>  'poprigun-chat-receiver-id',
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

<div id="poprigun-chat-dialog" class="hide" type="text/x-handlebars-template">
{{#each this}}
    <li class="media poprigun-chat-dialog-child poprigun-chat-dialog-id" data-dialog="{{dialog_id}}">
        <div class="media-message">
            <div class="media">
                <a class="pull-left" href="#">
                    <img class="media-object img-circle" style="height:40px;" src="{{image}}">
                </a>
                <div class="media-body">
                    <h5>{{user_name}}</h5>
                    <small class="text-muted">{{last_message}}</small>
                </div>
            </div>
        </div>
    </li>
{{/each}}
</div>

<div id="poprigun-chat-message" class="hide" type="text/x-handlebars-template">
{{#each this}}
    <li class="media poprigun-chat-message-child" data-message="{{message_id}}">
        <div class="media-message">
            <div class="media">
                <a class="pull-left" href="{{link}}">
                    <img class="media-object chat-image" src="{{user_avatar}}">
                </a>
                <div class="media-body">
                    <div class="message-text">{{message}}</div>
                    <br>
                    <small class="text-muted">{{user_name}}</small>
                    <hr>
                </div>
            </div>
        </div>
    </li>
{{/each}}
</div>
