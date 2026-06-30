<?php

use yii\db\Migration;

class m260101_000000_create_visitor extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%visitor}}', [
            'id' => $this->primaryKey(),
            'note' => $this->string(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%visitor}}');
    }
}
