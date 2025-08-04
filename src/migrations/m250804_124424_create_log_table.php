<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%log}}`.
 */
class m250804_124424_create_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%log}}', [
            'id' => $this->primaryKey(),
            'ip' => $this->string(45)->notNull(),
            'requested_at' => $this->dateTime()->notNull(),
            'url' => $this->text()->notNull(),
            'user_agent' => $this->text()->notNull(),
            'os' => $this->string()->notNull(),
            'architecture' => $this->string(10)->notNull(),
            'browser' => $this->string()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%log}}');
    }
}
