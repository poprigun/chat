<div class="poprigun-chat" data-show-avatar="<?= isset($options['showUserAvatar']) ? $options['showUserAvatar'] : true ?>" data-show-last_message="<?= isset($options['showLastImage']) ? $options['showLastImage'] : true ?>"  data-count="<?= isset($options['count']) ? $options['count'] : \poprigun\chat\Chat::$defaultCount ?>" >
    <div class="col-sm-12">

        <div class="col-sm-4">
            <div class="panel panel-primary">
                <div class="panel-heading">Dialogs</div>
                <div class="panel-body poprigun-chat-dialogs">
                    <ul id="poprigun-chat-dialog-block" class="media-list"></ul>
                </div>
            </div>
        </div>

        <div class="col-sm-8">
            <div class="panel panel-primary">
                <div class="panel-heading">Chat</div>
                <div class="panel-body poprigun-chat-message-ajax" id="poprigun-chat-message-ajax">
                    <ul id="poprigun-chat-message-block" class="media-list"></ul>
                    <div id="poprigun-chat-scroll-down"></div>
                </div>
                <div class="panel-footer">
                    <div class="input-group">
                        <input id="poprigun-chat-text" class="form-control" placeholder="Enter Message" type="text">
                        <span class="input-group-btn">
                            <button id="poprigun-chat-send" class="btn btn-info" type="button">SEND</button>
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div id="poprigun-chat-dialog" class="hide" >
    <li class="media">
        <div class="media-message poprigun-chat-dialog-id" data-block-dialog_id="data-dialog">
            <div class="media">
                <a class="pull-left poprigun-chat-show" href="#">
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
                    <img data-block-user_avatar="src" class="media-object poprigun-chat-image" src="">
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
