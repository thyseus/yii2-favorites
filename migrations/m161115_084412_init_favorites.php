<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * @author Herbert Maschke <thyseus@gmail.com
 */
class m161115_084412_init_favorites extends Migration
{
    public function up()
    {
        $tableOptions = '';

        if (Yii::$app->db->driverName == 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%favorites}}', [
            'id'                   => Schema::TYPE_PK,
            'created_by'           => Schema::TYPE_INTEGER,
            'updated_by'           => Schema::TYPE_INTEGER,
            'created_at'           => Schema::TYPE_DATETIME,
            'updated_at'           => Schema::TYPE_DATETIME,
            'model'                => Schema::TYPE_TEXT,
            'target_id'            => Schema::TYPE_TEXT, // can be a slug, not only an numeric id
            'target_attribute'     => Schema::TYPE_TEXT,
            'route'                => Schema::TYPE_TEXT,
            'url'                  => Schema::TYPE_TEXT,
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%favorites}}');
    }
}
