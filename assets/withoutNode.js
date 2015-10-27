var poprigunChat;
PoprigunChat = function(options) {

    var self = this;
    var messageUpdate = null;
    var dialogUpdate = null;

    this.settings = {};

    this.settings.cid = false;
    this.settings.cidDialog = false;
    this.settings.form = false;
    this.settings.callback = {};

    this.settings.dateFormat = false;
    this.settings.userTimeZone = true;

    this.settings.sendByEnter = true;
    this.settings.clearEntryField = true;
    this.settings.loaded = false;
    this.settings.loading = false;
    this.settings.datatype = 'json';
    this.settings.typeSend = 'POST';
    this.settings.typeLoad = 'GET';

    this.settings.messageTime = 3000;
    this.settings.dialogTime = 7000;

    this.settings.addNewMessage = 'append';
    this.settings.addOldMessage = 'prepend';

    this.settings.scrollBox = '.poprigun-chat-message-ajax';

    this.settings.entryField = '.poprigun-chat-form-message';
    this.settings.receiverField = '.poprigun-chat-receiver-id';

    this.settings.sourceDialog = '#poprigun-chat-dialog';
    this.settings.parentDialog = '#poprigun-chat-dialog-block';
    this.settings.childrenDialog = '.poprigun-chat-dialog-child';

    this.settings.sourceMessage = '#poprigun-chat-message';
    this.settings.parentMessage = '#poprigun-chat-message-block';
    this.settings.childrenMessage = '.poprigun-chat-message-child';

    this.settings.urlGetDialogs = '/poprigun_chat/chat/get-dialogs';
    this.settings.urlGetDialogMessages = '/poprigun_chat/chat/get-dialog-messages';
    this.settings.urlDeleteDialog = '/poprigun_chat/chat/delete-dialog';
    this.settings.urlSendMessage = '/poprigun_chat/chat/send-message';

    init(options);
    // init settings
    function init (options) {
        if(typeof options == 'object'){
            $.each(options, function (key, value) {
                self.settings[key] = value;
            });
        }

        buidlWrapper();
        sendByEnter(self.settings.sendByEnter);
        self.settings.scrollBox.on('scroll',function(){
            scrollBlock()
        });
        self.settings.form.on('submit', function (event) {
            event.preventDefault();
            self.send();
        });
    }
    // send message by key "Enter"
    function sendByEnter(send){
        if(send){
            self.settings.entryField.on('keypress',function(e){
                var dialogId =  self.settings.parentMessage.data('dialog');
                if(e.keyCode == 13){
                    self.settings.form.submit();
                }
            });
        }
    }
    // scroll messages
    function scrollBlock() {

        var scroll = self.settings.scrollBox.scrollTop();
        var height = self.settings.scrollBox.height();

        if (height + scroll == height) {
            var message = self.firstMessage();
            var dialogId = self.settings.parentMessage.data('dialog');

            self.loadOldMessage({
                dialogId: dialogId,
                messageId : message.data('message'),
                old : true
            });
        }
    }
    // jquery wrapper
    function buidlWrapper(){
        self.settings.form = $(self.settings.form);

        self.settings.scrollBox = $(self.settings.scrollBox);

        self.settings.entryField  = $(self.settings.entryField);
        self.settings.receiverField  = $(self.settings.receiverField);

        self.settings.sourceDialog = $(self.settings.sourceDialog);
        self.settings.parentDialog = $(self.settings.parentDialog);

        self.settings.sourceMessage = $(self.settings.sourceMessage);
        self.settings.parentMessage = $(self.settings.parentMessage);
    }
    // add item
    function addItem(data, source, parent, old){
        var template = Handlebars.compile( source.html() );
        if(old == undefined){
            if(self.settings.addNewMessage == 'append'){
                parent.append( template(data) );
            }else{
                parent.prepend( template(data) );
            }
        }else{
            if(self.settings.addOldMessage == 'prepend'){
                parent.prepend( template(data) );
            }else{
                parent.append( template(data) );
            }
        }
    }

    // send message
    this.send = function () {
        $.ajax({
            url: self.settings.urlSendMessage,
            type: self.settings.typeSend,
            dataType: self.settings.datatype,
            data: self.settings.form.serialize(),
            beforeSend: function (xhr,settings) {
                return self.sendBeforeSendCallback(xhr,settings);
            },
            complete: function (xhr,textStatus) {
                self.sendCompleteCallback(xhr,textStatus);
            },
            success: function (data) {
                addItem(data, self.settings.sourceMessage, self.settings.parentMessage);
                if(self.settings.clearEntryField){
                    self.settings.entryField.val('');
                }

                self.settings.scrollBox.scrollTop(self.settings.scrollBox[0].scrollHeight);
                self.sendSuccessCallback(data);
            },
            error: function (xhr,textStatus,errorThrown) {
                self.sendErrorCallback(xhr,textStatus,errorThrown);
            }
        });
    }
    // load messages
    this.load = function (options, callback) {

        options = options || {};

        if(options.loadUrl == undefined){
            options.loadUrl = self.settings.urlGetDialogs;
        }

        if(self.settings.loading == false /*&& self.settings.loaded == false*/ ) {
            self.settings.loading = true;
            $.ajax({
                url: options.loadUrl,
                type: self.settings.typeLoad,
                dataType: self.settings.datatype,
                data: options,
                beforeSend: function (xhr, settings) {
                    return self.loadBeforeSendCallback(xhr, settings);
                },
                complete: function (xhr, textStatus) {
                    self.settings.loading = false;
                    self.loadCompleteCallback(xhr, textStatus);
                },
                success: function (data) {
                    callback(self, data);
                    self.loadSuccessCallback(data);
                },
                error: function (xhr, textStatus) {
                    self.loadErrorCallback(xhr, textStatus);
                }
            });
        }
    }
    // load messages (new|received)
    this.loadMessage = function (options,drop) {
        options = options || {};

        if(options.loadUrl == undefined){
            options.loadUrl = self.settings.urlGetDialogMessages;
        }
        if(drop != undefined){
            self.settings.parentMessage.empty();
        }

        self.load(options,function(self,data){
            addItem(data, self.settings.sourceMessage, self.settings.parentMessage);
            self.settings.scrollBox.scrollTop(self.settings.scrollBox[0].scrollHeight);
        });
    }
    // load old messages
    this.loadOldMessage = function (options) {
        options = options || {};

        if(options.loadUrl == undefined){
            options.loadUrl = self.settings.urlGetDialogMessages;
        }

        self.load(options,function(self,data){
            if (data.length == 0) {
                self.settings.loaded = true;
            } else {
                addItem(data, self.settings.sourceMessage, self.settings.parentMessage, true);
            }
        });
    }
    // load dialogs
    this.loadDialogs = function(options){
        options = options || {};

        if(options.loadUrl == undefined){
            options.loadUrl = self.settings.urlGetDialogs;
        }
        self.load(options, function(self, data){
            self.settings.parentDialog.empty();
            addItem(data, self.settings.sourceDialog, self.settings.parentDialog);
        });
    }

    // complete send callback
    this.sendCompleteCallback = function (xhr, textStatus) {
        if( self.settings.callback.sendCompleteCallback != undefined){
            self.settings.callback.sendCompleteCallback(xhr, textStatus);
        }
    }
    // success send callback
    this.sendSuccessCallback = function (data) {
        if( self.settings.callback.sendSuccessCallback != undefined){
            self.settings.callback.sendSuccessCallback(data);
        }
    }
    // before send sendCallback
    this.sendBeforeSendCallback = function (xhr,settings) {

        if(self.settings.callback.sendBeforeSendCallback != undefined){
            return self.settings.callback.sendBeforeSendCallback(xhr,settings);
        }else{
            if(!self.settings.entryField.val().trim().length){
                return false;
            }
        }
        return true;
    }
    // error send Callback
    this.sendErrorCallback = function (xhr,textStatus) {
        if( self.settings.callback.sendErrorCallback != undefined){
            self.settings.callback.sendErrorCallback(xhr,textStatus);
        }
    }

    // complete load callback#if
    this.loadCompleteCallback = function (xhr, textStatus) {
        if( self.settings.callback.loadCompleteCallback != undefined){
            self.settings.callback.loadCompleteCallback(xhr, textStatus);
        }
    }
    // success load callback
    this.loadSuccessCallback = function (data) {
        if( self.settings.callback.loadSuccessCallback != undefined){
            self.settings.callback.loadSuccessCallback(data);
        }
    }
    // before load sendCallback
    this.loadBeforeSendCallback = function (xhr,settings) {
        if( self.settings.callback.loadBeforeSendCallback != undefined){
            return self.settings.callback.loadBeforeSendCallback(xhr,settings);
        }
        return true;
    }
    // error load Callback
    this.loadErrorCallback = function (xhr,textStatus) {
        if( self.settings.callback.loadErrorCallback != undefined){
            self.settings.callback.loadErrorCallback(xhr,textStatus);
        }
    }
    // return first message object
    this.firstMessage = function () {
        return self.settings.parentMessage.find(self.settings.childrenMessage + ':first-child');
    }
    // return last message object
    this.lastMessage = function () {
        return self.settings.parentMessage.find(self.settings.childrenMessage+ ':last-child');
    }
    //Listen dialog message
    this.listenServerMessage = function(dialogId){
        clearInterval(self.messageUpdate);
        if(self.settings.messageTime == 'undefined' || self.settings.messageTime == 0){
            return false;
        }

        self.messageUpdate = setInterval(function(){
            var message = self.lastMessage();

            self.loadMessage({
                dialogId : dialogId,
                messageId:  message.data('message')
            })
        },self.settings.messageTime);
    };
    //Listen dialogs
    this.listenServerDialog = function(){
        clearInterval(self.dialogUpdate);
        if(self.settings.dialogTime == 'undefined' || self.settings.dialogTime == 0){
            return false;
        }
        self.dialogUpdate = setInterval(function(){
            self.loadDialogs();
        },self.settings.dialogTime);
    };
    // open dialog messages
    self.settings.parentDialog.on('click','> *',function(event){
        event.preventDefault();

        var dialogId = $(this).data('dialog');
        self.settings.parentMessage.data('dialog',dialogId);
        self.loadMessage({
            dialogId : dialogId,
        },true)
        self.settings.receiverField.val(dialogId);
        self.listenServerMessage(dialogId);
    });
    // delete dialog
    this.deleteDialog = function(){
        var dialogId = self.settings.parentMessage.dasta('dialog');
        if(dialogId.length){
            $.get(self.settings.urlDeleteDialog, {'dialogId': dialogId}, function(data){
                if(data.status == 'success'){
                    self.messageOldCount = 0;
                    self.settings.parentMessage.data('messages',0);
                    self.settings.parentDialog.find('[data-dialog='+dialogId+']').trigger('click');
                }
            })
        }
    };
};
