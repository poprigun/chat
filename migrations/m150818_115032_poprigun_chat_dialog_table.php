<?php

use yii\db\Migration;
use yii\db\Schema;

class m150818_115032_poprigun_chat_dialog_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB AUTO_INCREMENT=0';
        }

        $this->createTable('{{%poprigun_chat_dialog}}', [
            'id'                       => 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'author_id'                => 'INT(11) UNSIGNED NOT NULL',
            'title'                    => 'VARCHAR(128) NULL DEFAULT NULL ',
            'type'                     => 'TINYINT(1) NOT NULL DEFAULT '.poprigun\chat\models\PoprigunChatDialog::TYPE_PERSONAL,
            'status'                   => 'TINYINT(1) NOT NULL DEFAULT '.poprigun\chat\models\PoprigunChatDialog::STATUS_ACTIVE,
            'updated_at'               => 'DATETIME DEFAULT NULL',
            'created_at'               => 'DATETIME DEFAULT NULL',
        ], $tableOptions);

        $this->createIndex('idx-poprigun_chat_dialog-author_id','{{%poprigun_chat_dialog}}','author_id');
    }

    public function down()
    {
        $this->dropTable('{{%poprigun_chat_dialog}}');
    }
}
