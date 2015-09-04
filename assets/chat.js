$(window).ready(function(){

    var PoprigunChat = function(options){

        var self = this;
        var messageUpdate = null;
        var dialogUpdate = null;
        var sendMessageStatus = false;
        var getDialogMessageStatus = false;
        var getNewMessageStatus = false;
        var displayNewMessage = 'down';
        var displayOldMessage = 'up';

        var defaultTime = 7000;
        var messageOldCount = 0;

        init(options);
        //settings
        this.showAvatar = ($('.poprigun-chat').data('show-avatar') == true) ? true : false;
        this.showLastMessage = ($('.poprigun-chat').data('show-last_message') == true) ? true : false;
        this.count = $('.poprigun-chat').data('count');
        this.sendByEnter = true;
        this.dateFormat = false;
        this.userTimeZone = true;
        this.dialogMessageTime = 3000;
        this.dialogTime = 7000;

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
            var object = $(source).children().clone();
            $.each(data, function(key, val){
                var $control = object.find('[data-block-'+key+']');

                if($control){
                    $control.each(function(id, block){
                        var attr = $(block).data('block-'+key);
                        if(attr){
                            if(attr == 'html')
                            {
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
                if(displayNewMessage == 'down'){
                    object.appendTo(parent);
                }else{
                    object.prependTo(parent);
                }
            }else{
                if(displayOldMessage == 'up'){
                    object.prependTo(parent);
                }else{
                    object.appendTo(parent);

                }
            }

        }

        //Listen dialog message
        this.listenServerMessage = function(){
            clearInterval(self.messageUpdate);
            if(self.dialogMessageTime == undefined){
                self.dialogMessageTime = self.defaultTime;
            }

            self.messageUpdate = setInterval(self.getDialogMessage,self.dialogMessageTime);
        }

        //Listen dialogs
        this.listenServerDialog = function(){
            clearInterval(self.dialogUpdate);
            if(self.dialogTime == undefined){
                self.dialogTime = self.defaultTime;
            }

            self.dialogUpdate = setInterval(self.getNewMessage,self.dialogTime);
        }

        //Get new messages
        this.getNewMessage = function(){
            if(!self.getNewMessageStatus){
                self.getNewMessageStatus = true;

                $.ajax({
                    url: '/pchat/get-dialogs',
                    type: 'POST',
                    data: {
                        options:  {
                            showAvatar: self.showAvatar,
                            showLastMessage: self.showLastMessage
                        }
                    },
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
        }

        //Get dialog message
        this.getDialogMessage = function(){
            var offset = $('#poprigun-chat-message-block').data('messages');
            var dialogId = $('#poprigun-chat-message-block').data('dialog');

            if(!self.getDialogMessageStatus){
                self.getDialogMessageStatus = true;

                $.ajax({
                    url: '/pchat/get-dialog-messages?dialogId='+dialogId,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        offset:  offset,
                        options:  {
                            showAvatar: self.showAvatar,
                            count: self.count
                        }
                    },
                    success: function (data) {
                        var count = parseInt($('#poprigun-chat-message-block').data('messages'));
                        if(!Number.isInteger(count)){
                            count = 0;
                        }
                        $.each(data,function(i,j){
                            j.date = dateTimeZone(j.date,self.dateFormat);
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
        }

        //Date format
        function dateTimeZone(messageDate, dateFormat){
            var date = new Date();
            var messageTime = new Date(messageDate*1000) ;

            if(self.userTimeZone){
                //messageTime = new Date(messageDate*1000) + date.getTimezoneOffset() * 60000;
            }

            //date format
            if(dateFormat){

            }

            dateTime = (messageTime.toTimeString()).slice(0,8);
            return messageTime.toDateString() +' ' + dateTime;
        }

        //scroll message
        function scrollMessages(parent, dialogId){

            var scrollBlock = $('#poprigun-chat-message-ajax');

            scrollBlock.scroll(function() {
                var scroll = scrollBlock.scrollTop();
                var height = scrollBlock.height();
                if (scroll + height == height) {
                    if(!$(parent).attr('loaded')) {
                        $(parent).attr('loaded', 'loaded');

                        $.ajax({
                            type: 'post',
                            url: '/pchat/get-dialog-messages?dialogId='+dialogId,
                            dataType: 'json',
                            data: {
                                offset : self.messageOldCount,
                                old : true,
                                options:  {
                                    showAvatar: self.showAvatar,
                                    count: self.count
                                }
                            },
                            success:function(data){
                                $.each(data,function(i,j){
                                    j.date = dateTimeZone(j.date,self.dateFormat);
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
                    $('#poprigun-chat-send').click();
                }
            });
        }

        //send message
        $('#poprigun-chat-send').on('click',function(){

            var inputValue = $('#poprigun-chat-text').val();
            var dialogId = $('#poprigun-chat-message-block').data('dialog');

            if(inputValue.length && !self.sendMessageStatus){

                self.sendMessageStatus = true;
                $.ajax({
                    url: '/pchat/send-message',
                    type: 'POST',
                    dataType: 'json',
                    data:{
                        id: dialogId,
                        message: inputValue,
                        type: 'dialog',
                        options:  {
                            showAvatar: self.showAvatar
                        }
                    },
                    success: function (data) {
                        $('#poprigun-chat-text').val('');
                        self.getDialogMessage();
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
        $('#poprigun-chat-dialog-block').on('click','.poprigun-chat-show',function(){
            var dialogId = $(this).parents('.poprigun-chat-dialog-id').data('dialog');
            $('#poprigun-chat-message-block').data('dialog',dialogId);
            resetOldMessage();

            $.ajax({
                url: '/pchat/get-dialog-messages?dialogId='+dialogId,
                type: 'POST',
                dataType: 'json',
                data: {
                    count: true,
                    options:  {
                        showAvatar: self.showAvatar,
                        count: self.count
                    }
                },
                success: function (data) {
                    $('#poprigun-chat-message-block').empty();
                    console.log(data)
                    $.each(data,function(i,j){
                        j.date = dateTimeZone(j.date,self.dateFormat);

                        if(i == 'count'){
                            $('#poprigun-chat-message-block').data('messages',j)
                        }else{
                            addItem(j,'#poprigun-chat-message','#poprigun-chat-message-block',true);
                            oldMessageIncrease();
                        }
                    });

                    self.listenServerMessage();

                    $('#poprigun-chat-message-ajax').off('scroll');
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

    var chat = new PoprigunChat();
    chat.getNewMessage();
    chat.listenServerDialog();
});
