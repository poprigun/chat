<?php

namespace poprigun\chat\migration;

use yii\db\Schema;
use yii\db\Migration;

class m150818_115142_poprigun_chat_user_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB AUTO_INCREMENT=0';
        }

        $this->createTable('{{%poprigun_chat_user}}', [
            'id'                       => 'INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'dialog_id'                => 'INT(11) NOT NULL',
            'user_id'                  => 'INT(11) UNSIGNED NOT NULL',
            'updated_at'               => 'DATETIME DEFAULT NULL',
            'created_at'               => 'DATETIME DEFAULT NULL',
        ], $tableOptions);

        $this->createIndex('idx-poprigun_chat_user-dialog_id','{{%poprigun_chat_user}}','dialog_id');
        $this->createIndex('idx-poprigun_chat_user-user_id','{{%poprigun_chat_user}}','user_id');

        $this->addForeignKey('fk-poprigun_chat_user-dialog_id', '{{%poprigun_chat_user}}', 'dialog_id', '{{%poprigun_chat_dialog}}', 'id','CASCADE','CASCADE');
        $this->addForeignKey('fk-poprigun_chat_user-user_id', '{{%poprigun_chat_user}}', 'user_id', '{{%user}}', 'id','CASCADE','CASCADE');

    }

    public function down()
    {
        $this->dropForeignKey('fk-poprigun_chat_user-dialog_id', '{{%poprigun_chat_user}}');
        $this->dropForeignKey('fk-poprigun_chat_user-user_id', '{{%poprigun_chat_user}}');
        $this->dropTable('{{%poprigun_chat_user}}');
    }
}
