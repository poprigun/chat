<?php

use yii\db\Schema;
use yii\db\Migration;

class m150818_115150_poprigun_chat_attachment_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB AUTO_INCREMENT=0';
        }

        $this->createTable('{{%poprigun_chat_attachment}}', [
            'id'                       => 'INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'message_id'               => 'INT(11) NOT NULL',
            'attachment'               => 'VARCHAR(500) NULL DEFAULT NULL',
            'updated_at'               => 'DATETIME DEFAULT NULL',
            'created_at'               => 'DATETIME DEFAULT NULL',
        ], $tableOptions);

        $this->createIndex('idx-poprigun_chat_attachment-message_id','{{%poprigun_chat_attachment}}','message_id');
        $this->addForeignKey('fk-poprigun_chat_attachment-message_id', '{{%poprigun_chat_attachment}}', 'message_id', '{{%poprigun_chat}}', 'id','CASCADE','CASCADE');

    }

    public function down()
    {
        $this->dropForeignKey('fk-poprigun_chat_attachment-message_id', '{{%poprigun_chat_attachment}}');
        $this->dropTable('{{%poprigun_chat_attachment}}');
    }
}
