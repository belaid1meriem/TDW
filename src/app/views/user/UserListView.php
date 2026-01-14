<?php
namespace App\Views\User;
use Core\AutoCrud\Views\ListView;
use Core\Components;

class UserListView extends ListView {

    protected function renderHeader(): string{
        ob_start();
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2><?= $this->escape($this->model->getTableLabel()) ?></h2>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function renderActions($id, $row): string
    {
        $showUrl = BASE_PATH . "/{$this->model->table}/{$id}";
        
        $showBtn = Components::Button([
            'text' => 'View',
            'size' => 'sm',
            'variant' => 'outline',
            'href' => $showUrl
        ]);
    
        
        return "<div class='flex gap-2'>{$showBtn}</div>";
    }


}