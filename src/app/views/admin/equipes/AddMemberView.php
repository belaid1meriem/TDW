<?php
namespace App\Views\Admin\Equipes;

use App\Views\Admin\AdminLayout;
use Core\Components;

class AddMemberView extends AdminLayout
{
    private $equipe;
    private $users;
    private $existingMemberIds;

    public function __construct($equipe, $users, $existingMemberIds)
    {
        $this->equipe = $equipe;
        $this->users = $users;
        $this->existingMemberIds = $existingMemberIds;
    }

    public function renderContent()
    {
        ?>
        <main class="flex flex-col gap-6">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Ajouter un membre à l'équipe "<?= htmlspecialchars($this->equipe['name'] ?? 'Équipe') ?>"</h2>
                <?= Components::Button([
                    'text' => '← Retour',
                    'variant' => 'outline',
                    'href' => $this->asset("admin/equipes/{$this->equipe['id']}")
                ]) ?>
            </div>
            
            <?php if ($this->hasFlash('errors')): ?>
                <div class="alert-container">
                    <?php 
                    $errors = $this->flash('errors'); 
                    foreach ($errors as $fieldErrors) {
                        foreach ($fieldErrors as $error) {
                            echo Components::Alert([
                                'variant' => 'destructive',
                                'message' => $error,
                                'dismissible' => true
                            ]);
                        }
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($this->hasFlash('error')): ?>
                <div class="alert-container">
                    <?php
                    echo Components::Alert([
                        'variant' => 'destructive',
                        'message' => $this->flash('error'),
                        'dismissible' => true
                    ]);
                    ?>
                </div>
            <?php endif; ?>

            <?= $this->renderMemberForm() ?>
        </main>
        <?php
    }

    private function renderMemberForm()
    {
        ob_start();
        ?>
        <form method="POST" action="<?= BASE_PATH."/admin/equipes/storeMember/{$this->equipe['id']}" ?>" id="memberForm">
            <?= Components::Card([
                'title' => 'Sélectionner un membre',
                'content' => $this->renderMemberSection()
            ]) ?>

            <div class="flex gap-4">
                <?= Components::Button([
                    'text' => 'Ajouter le membre',
                    'type' => 'submit',
                    'variant' => 'default',
                    'class' => 'w-full'
                ]) ?>
                
                <?= Components::Button([
                    'text' => 'Annuler',
                    'type' => 'button',
                    'variant' => 'outline',
                    'class' => 'w-full',
                    'attrs' => [
                        'onclick' => "window.location.href='" . $this->asset("admin/equipes/view/{$this->equipe['id']}") . "'"
                    ]
                ]) ?>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    private function renderMemberSection()
    {
        ob_start();
        
        // Prepare user options
        $userOptions = [];
        foreach ($this->users as $user) {
            // Skip users who are already members
            if (in_array($user['id'], $this->existingMemberIds)) {
                continue;
            }
            
            $userOptions[$user['id']] = $user['last_name'] . ' ' . $user['first_name'] . 
                                        ' (' . ($user['role'] ?? 'N/A') . ')';
        }
        
        if (empty($userOptions)) {
            ?>
            <div class="text-center text-muted" style="padding: 2rem;">
                <p>Tous les utilisateurs sont déjà membres de cette équipe.</p>
            </div>
            <?php
        } else {
            ?>
            <div style="display: grid; gap: 1rem;">
                <?= Components::Select([
                    'name' => 'user_id',
                    'label' => 'Utilisateur',
                    'options' => $userOptions,
                    'value' => $this->old('user_id'),
                    'error' => $this->hasError('user_id') ? $this->error('user_id') : '',
                    'placeholder' => 'Sélectionner un utilisateur',
                    'required' => true
                ]) ?>
                
                <p class="text-sm text-muted">
                    Sélectionnez l'utilisateur que vous souhaitez ajouter à cette équipe.
                </p>
            </div>
            <?php
        }
        
        return ob_get_clean();
    }
}
?>

<style>
    .alert-container {
        margin-bottom: 1.5rem;
    }

    #memberForm .card {
        margin-bottom: 1.5rem;
    }

    .text-muted {
        color: hsl(var(--muted-foreground));
    }

    .text-center {
        text-align: center;
    }

    @media (max-width: 768px) {
        #memberForm [style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>