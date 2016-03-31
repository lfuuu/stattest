<?php

class m160330_111743_append_tt_state extends \app\classes\Migration
{
    public function up()
    {
        $this->update(
            'tt_types',
            [
                'folders' => 70368744701697,
                'states' => 70368744310783,
            ],
            ['code' => 'trouble']
        );
        $this->insert('tt_folders', [
            'pk' => 70368744177664,
            'name' => 'Открыт повторно',
            'order' => 11,
        ]);
        $this->insert('tt_states', [
            'pk' => 70368744177664,
            'name' => 'Открыт повторно',
            'order' => 10,
            'time_delta' => 1,
            'folder' => 70368744177664,
        ]);
        $this->update(
            'tt_folders',
            [
                'name' => 'Проверка документов',
            ],
            ['pk' => 35184372088832]
        );
        $this->alterColumn('support_ticket', 'status', 'ENUM("open","done","closed","reopened") NOT NULL');
    }

    public function down()
    {
        $this->update(
            'tt_types',
            [
                'folders' => 524033,
                'states' => 133119,
            ],
            ['code' => 'trouble']
        );
        $this->delete('tt_folder', ['pk' => 70368744177664]);
        $this->delete('tt_states', ['pk' => 70368744177664]);
        $this->update(
            'tt_folders',
            [
                'name' => 'Проверка докeументов',
            ],
            ['pk' => 35184372088832]
        );
        $this->alterColumn('support_ticket', 'status', 'ENUM("open","done","closed") NOT NULL');
    }
}