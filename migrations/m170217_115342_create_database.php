<?php

use yii\db\Migration;

class m170217_115342_create_database extends Migration {

    public function up() {
        $this->createTable('album_files', [
            'album_files_id' => $this->primaryKey(),
            'album_id' => $this->integer() . ' UNSIGNED NOT NULL',
            'file_id' => $this->integer() . ' UNSIGNED NOT NULL',
                ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );
        $this->createIndex('gallery_file', 'album_files', ['album_id', 'file_id']);

        $this->createTable('albums', [
            'album_id' => $this->primaryKey(),
            'name' => $this->string(255) . ' NOT NULL',
                ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );

        $this->createTable('file_info', [
            'file_id' => $this->primaryKey(),
            'key' => "enum('exif_create_timestamp','exif_description','exif_title','exif_keywords','width','height') NOT NULL DEFAULT 'exif_create_timestamp'",
            'value' => $this->text(),
                ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createTable('files', [
            'file_id' => $this->primaryKey(),
            'folder_id' => $this->integer() . ' UNSIGNED NOT NULL',
            'original_name' => $this->char(255) . ' NOT NULL',
            'md_path' => $this->char(32) . ' NOT NULL',
            'md_content' => $this->char(32) . ' NOT NULL',
            'processed' => $this->integer(1) . ' UNSIGNED NOT NULL DEFAULT 1',
                ], 'ENGINE=InnoDB  DEFAULT CHARSET=utf8'
        );

        $this->createIndex('md_content', 'files', 'md_content', 1);
        $this->createIndex('md_path', 'files', 'md_path', 1);
        $this->createIndex('folder_id', 'files', 'folder_id');
        $this->createIndex('processed', 'files', 'processed');

        $this->createTable('folders', [
            'folder_id' => $this->primaryKey(),
            'name' => $this->string(255) . ' NOT NULL',
            'level' => $this->integer() . ' UNSIGNED NOT NULL',
            'left' => $this->integer() . ' UNSIGNED NOT NULL',
            'right' => $this->integer() . ' UNSIGNED NOT NULL',
                ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );
        $this->createIndex('level', 'folders', 'level');
        $this->createIndex('left', 'folders', 'left');
        $this->createIndex('right', 'folders', 'right');

        $this->createTable('folder_property', [
            'folder_id' => $this->integer() . ' UNSIGNED NOT NULL',
            'access_code' => $this->char(32) . ' DEFAULT NULL',
                ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );

        $this->createIndex('folder_id', 'folder_property', 'folder_id', 1);
        $this->createIndex('access_code', 'folder_property', 'access_code', 1);
    }

    public function down() {
        echo "m170217_115342_create_database cannot be reverted.\n";

        return false;
    }

}
