<?php
use poprigun\chat\models\PoprigunChatUserRel;
use yii\db\Migration;
class m150907_115345_poprigun_chat_user_rel_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB AUTO_INCREMENT=0';
        }
        $this->createTable('{{%poprigun_chat_user_rel}}', [
            'id'                    => 'INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'message_id'            => 'INT(11) NOT NULL',
            'user_id'               => 'INT(11) UNSIGNED NOT NULL',
            'view'                  => 'TINYINT(1) NOT NULL DEFAULT '. PoprigunChatUserRel::NEW_MESSAGE,
            'status'                => 'TINYINT(1) NOT NULL DEFAULT '. PoprigunChatUserRel::STATUS_ACTIVE,
        ], $tableOptions);
        $this->createIndex('idx-poprigun_chat_user_rel','{{%poprigun_chat_user_rel}}','message_id, user_id');
        $this->addForeignKey('fk-poprigun_chat_user_rel-message_id', '{{%poprigun_chat_user_rel}}', 'message_id', '{{%poprigun_chat_message}}', 'id','CASCADE','CASCADE');
        $this->addForeignKey('fk-poprigun_chat_user_rel-user_id', '{{%poprigun_chat_user_rel}}', 'user_id', '{{%poprigun_chat_user}}', 'user_id','CASCADE','CASCADE');
    }
    public function down()
    {
        $this->dropForeignKey('fk-poprigun_chat_user_rel-message_id', '{{%poprigun_chat_user_rel}}');
        $this->dropForeignKey('fk-poprigun_chat_user_rel-user_id', '{{%poprigun_chat_user_rel}}');
        $this->dropTable('{{%poprigun_chat_user_rel}}');
    }
}