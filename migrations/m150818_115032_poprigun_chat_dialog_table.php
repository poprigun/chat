<?php

namespace poprigun\chat\migrations;

use poprigun\chat\models\PoprigunChatDialog;
use yii\db\Schema;
use yii\db\Migration;

class m150818_115032_poprigun_chat_dialog_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB AUTO_INCREMENT=0';
        }

        $this->createTable('{{%poprigun_chat_dialog}}', [
            'id'                       => 'INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'user_id'                  => 'INT(11) UNSIGNED NOT NULL',
            'title'                    => 'VARCHAR(128) NULL DEFAULT NULL ',
            'group'                    => 'TINYINT(1) NOT NULL DEFAULT '.PoprigunChatDialog::GROUP_FALSE,
            'status'                   => 'TINYINT(1) NOT NULL DEFAULT '.PoprigunChatDialog::STATUS_ACTIVE,
            'updated_at'               => 'DATETIME DEFAULT NULL',
            'created_at'               => 'DATETIME DEFAULT NULL',
        ], $tableOptions);

        $this->createIndex('idx-poprigun_chat_dialog-user_id','{{%poprigun_chat_dialog}}','user_id');
        $this->addForeignKey('fk-poprigun_chat_dialog-user_id', '{{%poprigun_chat_dialog}}', 'user_id', '{{%user}}', 'id','CASCADE','CASCADE');

    }

    public function down()
    {
        $this->dropForeignKey('fk-poprigun_chat_dialog-user_id', '{{%poprigun_chat_dialog}}');
        $this->dropTable('{{%poprigun_chat_dialog}}');
    }
}
