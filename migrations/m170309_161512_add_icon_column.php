<?php

use yii\db\Migration;

/**
 * @author Herbert Maschke <thyseus@gmail.com>
 */
class m170309_161512_add_icon_column extends Migration
{
    public function up()
    {
        $this->addColumn('favorites', 'icon', $this->string());
    }

    public function down()
    {
        $this->dropColumn('favorites', 'icon');
    }
}
