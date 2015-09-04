<?php

/* @var $this \yii\web\View */

use poprigun\chat\models\PoprigunChat;
use yii\db\Schema;
use yii\db\Migration;

class m150818_115092_poprigun_chat_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB AUTO_INCREMENT=0';
        }
        $this->createTable('{{%poprigun_chat}}', [
            'id'                       => 'INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'dialog_id'                => 'INT(11) NOT NULL',
            'user_id'                  => 'INT(11) UNSIGNED NOT NULL',
            'message'                  => 'VARCHAR(2000) NULL DEFAULT NULL ',
            'view'                     => 'TINYINT(1) NOT NULL DEFAULT '.PoprigunChat::NEW_MESSAGE,
            'status'                   => 'TINYINT(1) NOT NULL DEFAULT '.PoprigunChat::STATUS_ACTIVE,
            'updated_at'               => 'DATETIME DEFAULT NULL',
            'created_at'               => 'DATETIME DEFAULT NULL',
        ], $tableOptions);

        $this->createIndex('idx-poprigun_chat-dialog_id','{{%poprigun_chat}}','dialog_id');
        $this->createIndex('idx-poprigun_chat-user_id','{{%poprigun_chat}}','user_id');

        $this->addForeignKey('fk-poprigun_chat-dialog_id', '{{%poprigun_chat}}', 'dialog_id', '{{%poprigun_chat_dialog}}', 'id','CASCADE','CASCADE');
        $this->addForeignKey('fk-poprigun_chat-user_id', '{{%poprigun_chat}}', 'user_id', '{{%user}}', 'id','CASCADE','CASCADE');

    }

    public function down()
    {
        $this->dropForeignKey('fk-poprigun_chat-dialog_id', '{{%poprigun_chat}}');
        $this->dropForeignKey('fk-poprigun_chat-user_id', '{{%poprigun_chat}}');
        $this->dropTable('{{%poprigun_chat}}');
    }
}
