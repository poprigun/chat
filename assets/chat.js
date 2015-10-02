var poprigunChat;
var socket = io($('#poprigun-chat').data('url'));

PoprigunChat = function(options){

    var self = this;
    var messageUpdate = null;
    var dialogUpdate = null;
    var sendMessageStatus = false;
    var getDialogMessageStatus = false;
    var getNewMessageStatus = false;
    var defaultTime = 7000;
    var messageOldCount = 0;

    init(options);

    //settings
    this.sendByEnter = true;
    this.dateFormat = false;
    this.userTimeZone = true;
    this.displayNewMessage = 'down';
    this.displayOldMessage = 'up';
    this.dialogMessageTime = 3000;
    this.dialogTime = 7000;
    this.urlGetDialogs = '/poprigun_chat/chat/get-dialogs';
    this.urlGetDialogMessages = '/poprigun_chat/chat/get-dialog-messages';
    this.urlDeleteDialog= '/poprigun_chat/chat/delete-dialog';
    this.urlSendMessage= '/poprigun_chat/chat/send-message';

    // init settings
    function init (options) {
        if(typeof options == 'object'){
            $.each(options, function (key, value) {
                self[key] = value;
            });
        }
    }

    // Add item
    function addItem(data, source, parent, newOld){
        var object = $(source).clone();

        $.each(data, function(key, val){
            var $control = object.find('[data-block-'+key+']');

            if($control){
                $control.each(function(id, block){
                    var attr = $(block).data('block-'+key);
                    if(attr){
                        if(attr == 'html'){
                            $(block).html(val)
                        }
                        else{
                            $(block).attr(attr, val);
                        }
                    }
                });
            }
        });
        if(newOld){
            if(self.displayNewMessage == 'down'){
                $(parent).append(object.html());
            }else{
                $(parent).prepend(object.html());
            }
        }else{
            if(self.displayOldMessage == 'up'){
                $(parent).prepend(object.html());
            }else{
                $(parent).append(object.html());
            }
        }
    }
    //Listen dialog message
    this.listenServerMessage = function(){
        clearInterval(self.messageUpdate);
        if(self.dialogMessageTime == undefined){
            self.dialogMessageTime = defaultTime;
        }
        self.messageUpdate = setInterval(self.getDialogMessage,self.dialogMessageTime);
    };

    //Listen dialogs
    this.listenServerDialog = function(){
        clearInterval(self.dialogUpdate);
        if(self.dialogTime == undefined){
            self.dialogTime = defaultTime;
        }
        self.dialogUpdate = setInterval(self.getNewMessage,self.dialogTime);
    };

    //Get new messages
    this.getNewMessage = function(){
        if(!self.getNewMessageStatus){
            self.getNewMessageStatus = true;

            $.ajax({
                url: self.urlGetDialogs,
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    $('#poprigun-chat-dialog-block').empty();
                    $.each(data,function(i,j){
                        addItem(j,'#poprigun-chat-dialog','#poprigun-chat-dialog-block',true);
                    });
                },
                error: function(error) {
                    console.log(error);
                },
            }).always(function() {
                self.getNewMessageStatus = false;
            });
        }
    };

    //Get dialog message
    this.getDialogMessage = function(){
        var offset = $('#poprigun-chat-message-block').data('messages');
        var dialogId = $('#poprigun-chat-message-block').data('dialog');

        if(!self.getDialogMessageStatus){
            self.getDialogMessageStatus = true;

            $.ajax({
                url: self.urlGetDialogMessages,
                type: 'GET',
                dataType: 'json',
                data: {
                    dialogId : dialogId,
                    offset:  offset
                },
                success: function (data) {
                    var count = parseInt($('#poprigun-chat-message-block').data('messages'));
                    if(!Number.isInteger(count)){
                        count = 0;
                    }
                    $.each(data,function(i,j){
                        j.date = dateTimeZone(j.date);
                        addItem(j,'#poprigun-chat-message','#poprigun-chat-message-block',true);
                        $('#poprigun-chat-message-block').data('messages',count + 1);
                        oldMessageIncrease();
                    });
                },
                error: function(error) {
                    console.log(error);
                }
            }).always(function(data) {
                self.getDialogMessageStatus = false;
            });
        }
    };

    //Date format
    function dateTimeZone(messageDate){
        //var date = new Date();
        //var dateFormat = self.dateFormat;
        //var messageTime = new Date(messageDate*1000) ;
        //
        //if(self.userTimeZone){
        //    //messageTime = new Date(messageDate*1000) + date.getTimezoneOffset() * 60000;
        //}
        ////date format
        //if(dateFormat){
        //
        //}
        //
        //dateTime = (messageTime.toTimeString()).slice(0,8);
        return messageDate;
    }

    //scroll message
    function scrollMessages(parent, dialogId){

        var scrollBlock = $('.poprigun-chat-message-ajax');

        scrollBlock.scroll(function() {
            var scroll = scrollBlock.scrollTop();
            var height = scrollBlock.height();
            if (scroll + height == height) {
                if(!$(parent).attr('loaded')) {
                    $(parent).attr('loaded', 'loaded');

                    $.ajax({
                        type: 'GET',
                        url: self.urlGetDialogMessages,
                        dataType: 'json',
                        data: {
                            dialogId: dialogId,
                            offset : self.messageOldCount,
                            old : true
                        },
                        success:function(data){
                            $.each(data,function(i,j){
                                j.date = dateTimeZone(j.date);
                                addItem(j,'#poprigun-chat-message','#poprigun-chat-message-block',false);
                                oldMessageIncrease();
                            });
                        },
                        error: function(error){
                            console.log(error)
                        }
                    }).always(function() {
                        $(parent).attr('loaded', '');
                    });
                }
            }
        });
    }

    //reset olf message count
    function resetOldMessage(){
        self.messageOldCount = 0;
    }

    //increase old message count
    function oldMessageIncrease(){
        self.messageOldCount += 1;
    }

    // send message by key "Enter"
    if(this.sendByEnter){
        $('#poprigun-chat-text').on('keypress',function(e){
            if(e.keyCode==13){
                $('#poprigun-chat-send-form').submit();
            }
        });
    }

    $('#poprigun-dialog-delete').click(function(){
        var dialogId = $('#poprigun-chat-message-block').data('dialog');
        $.get(self.urlDeleteDialog, {'dialogId': dialogId}, function(data){
            if(data.status == 'success'){
                self.messageOldCount = 0;
                $('#poprigun-chat-message-block').data('messages',0);
                $('.poprigun-chat-dialog-id').trigger('click');
            }
        })
    });

    //send message
    $(document).on('submit','#poprigun-chat-send-form',function(event){

        event.preventDefault();
        event.stopPropagation();

        var dialogId = $('#poprigun-chat-message-block').data('dialog');
        var that = this;
        if(!self.sendMessageStatus){

            self.sendMessageStatus = true;
            $.ajax({
                url: self.urlSendMessage,
                type: 'POST',
                dataType: 'json',
                data: $(that).serialize(),
                success: function (data) {

                    socket.emit('message', 'sdfgsdfgdfgdfgsdfgfdg');

                    $('.poprigun-chat-form-message').val('');
                    var count = parseInt($('#poprigun-chat-message-block').data('messages'));
                    if(!Number.isInteger(count)){
                        count = 0;
                    }
                    $.each(data,function(i,j){
                        j.date = dateTimeZone(j.date);

                        addItem(j,'#poprigun-chat-message','#poprigun-chat-message-block',true);
                        $('#poprigun-chat-message-block').data('messages',count + 1);
                        oldMessageIncrease();
                    });
                },
                error: function(error) {
                    console.log(error);
                }
            }).always(function() {
                self.sendMessageStatus = false;
            });
        }
    });

    //show dialog message
    $('#poprigun-chat-dialog-block').on('click','.poprigun-chat-dialog-id',function(){
        var dialogId = $(this).data('dialog');
        $('#poprigun-chat-message-block').data('dialog',dialogId);
        resetOldMessage();

        $.ajax({
            url: self.urlGetDialogMessages,
            type: 'GET',
            dataType: 'json',
            data: {
                dialogId :dialogId,
                count: true
            },
            success: function (data) {
                $('#poprigun-chat-message-block').empty();
                $.each(data,function(i,j){
                    j.date = dateTimeZone(j.date);

                    if(i == 'count'){
                        $('#poprigun-chat-message-block').data('messages',j)
                    }else{
                        addItem(j,'#poprigun-chat-message','#poprigun-chat-message-block',true);
                        oldMessageIncrease();
                    }
                });
                $('.poprigun-chat-receiver-id').val(dialogId);
                self.listenServerMessage();

                $('.poprigun-chat-message-ajax').off('scroll');
                scrollMessages('#poprigun-chat-message-block',dialogId);
            },
            error: function(error) {
                console.log(error);
            }
        });
        return false;
    });

    return this;
};

$(window).ready(function(){
    poprigunChat = new PoprigunChat();
    poprigunChat.getNewMessage();
    poprigunChat.listenServerDialog();
});
