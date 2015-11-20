<?php

/* @var $this \yii\web\View */

use poprigun\chat\models\PoprigunChatMessage;
use yii\db\Schema;
use yii\db\Migration;

class m150818_115092_poprigun_chat_message_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB AUTO_INCREMENT=0';
        }
        $this->createTable('{{%poprigun_chat_message}}', [
            'id'                       => 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'dialog_id'                => 'INT(11) UNSIGNED NOT NULL',
            'author_id'                => 'INT(11) UNSIGNED NOT NULL',
            'message'                  => 'TEXT NULL DEFAULT NULL ',
            'updated_at'               => 'DATETIME DEFAULT NULL',
            'created_at'               => 'DATETIME DEFAULT NULL',
        ], $tableOptions);

        $this->createIndex('idx-poprigun_chat_message-dialog_id','{{%poprigun_chat_message}}','dialog_id');
        $this->createIndex('idx-poprigun_chat_message-author_id','{{%poprigun_chat_message}}','author_id');

        $this->addForeignKey('fk-poprigun_chat_message-dialog_id', '{{%poprigun_chat_message}}', 'dialog_id', '{{%poprigun_chat_dialog}}', 'id','CASCADE','CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk-poprigun_chat_message-dialog_id', '{{%poprigun_chat_message}}');
        $this->dropTable('{{%poprigun_chat_message}}');
    }
}
