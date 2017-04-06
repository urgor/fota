<?php

use yii\db\Migration;

class m170406_080342_alterFileInfo extends Migration
{
    public function up()
    {
        $this->alterColumn('file_info', 'file_id', $this->integer() . ' not null');
        $this->dropPrimaryKey('primary', 'file_info');
        $this->createIndex('file_id_key', 'file_info', ['file_id', 'key'], true);
    }

    public function down()
    {
        $this->dropIndex('file_id_key', 'file_info');
        $this->alterColumn('file_info', 'file_id', $this->integer() . ' not null autoincrement');
        $this->addPrimaryKey('file_id', 'file_info', 'file_id');

        return true;
    }
}
