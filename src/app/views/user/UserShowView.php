<?php
namespace App\Views\User;
use Core\AutoCrud\Views\ShowView;
use Core\Components;

class UserShowView extends ShowView {

protected function renderHeader(): string
    {
        $pkValue = $this->getPrimaryKeyValue();
        $backUrl = BASE_PATH . "/{$this->model->table}";
        
        ob_start();
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2><?= $this->escape($this->model->getTableLabel()) ?> Details</h2>
            
            <div class="flex gap-2">
                <?= Components::Button([
                    'text' => '← Back',
                    'variant' => 'outline',
                    'href' => $backUrl
                ]) ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }


}