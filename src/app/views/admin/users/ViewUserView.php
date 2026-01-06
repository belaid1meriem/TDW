<?php
namespace App\Views\Admin\Users;
use App\Views\Admin\AdminLayout;
use Core\Components;

class ViewUserView extends AdminLayout
{
    private $user;

    public function __construct($user)
    {
        parent::__construct();
        $this->user = $user;
    }

    public function renderContent()
    {
        $user = $this->user;
        
        ?>
        <main class="flex flex-col gap-6">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Détails de l'utilisateur</h2>
                <div class="flex gap-2">
                    <?= Components::Button([
                        'text' => 'Modifier',
                        'variant' => 'default',
                        'href' => '/admin/users/edit/' . $user['id']
                    ]) ?>
                    <?= Components::Button([
                        'text' => '← Retour',
                        'variant' => 'outline',
                        'href' => '/admin/users'
                    ]) ?>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
                <?= $this->renderProfileCard() ?>
                <?= $this->renderDetailsCard() ?>
            </div>

            <?= $this->renderAcademicCard() ?>
        </main>
        <?php
    }

    private function renderProfileCard()
    {
        $user = $this->user;
        
        ob_start();
        echo Components::Card([
            'content' => $this->renderProfileContent()
        ]);
        return ob_get_clean();
    }

    private function renderProfileContent()
    {
        $user = $this->user;
        
        ob_start();
        ?>
        <div style="text-align: center;">
            <?php if ($user['photo']): ?>
                <img src="<?= $this->asset('images/' . $user['photo']) ?>" 
                     alt="<?= htmlspecialchars($user['first_name']) ?>" 
                     style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem;">
            <?php else: ?>
                <?= Components::Avatar([
                    'alt' => $user['first_name'] . ' ' . $user['last_name'],
                    'fallback' => strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)),
                    'size' => 'lg'
                ]) ?>
            <?php endif; ?>

            <h3 style="margin-bottom: 0.5rem;">
                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
            </h3>
            
            <p class="text-muted" style="margin-bottom: 1rem;">
                @<?= htmlspecialchars($user['username']) ?>
            </p>

            <?= $this->renderRoleBadge($user['role']) ?>
            <?= $this->renderStatusBadge($user['status']) ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderDetailsCard()
    {
        $user = $this->user;
        
        ob_start();
        echo Components::Card([
            'title' => 'Informations',
            'content' => $this->renderDetailsContent()
        ]);
        return ob_get_clean();
    }

    private function renderDetailsContent()
    {
        $user = $this->user;
        
        ob_start();
        ?>
        <div style="display: grid; gap: 1rem;">
            <?= $this->renderInfoRow('Email', $user['email']) ?>
            <?= Components::Separator() ?>
            <?= $this->renderInfoRow('Nom d\'utilisateur', $user['username']) ?>
            <?= Components::Separator() ?>
            <?= $this->renderInfoRow('Grade', $user['grade'] ?: 'Non spécifié') ?>
            <?= Components::Separator() ?>
            <?= $this->renderInfoRow('Poste', $user['poste'] ?: 'Non spécifié') ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderAcademicCard()
    {
        $user = $this->user;
        
        ob_start();
        echo Components::Card([
            'title' => 'Informations académiques',
            'content' => $this->renderAcademicContent()
        ]);
        return ob_get_clean();
    }

    private function renderAcademicContent()
    {
        $user = $this->user;
        
        ob_start();
        ?>
        <div style="display: grid; gap: 1.5rem;">
            <?php if ($user['domaine_recherche']): ?>
                <div>
                    <h4 class="font-medium mb-2">Domaine de recherche</h4>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($user['domaine_recherche'])) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($user['bio']): ?>
                <div>
                    <h4 class="font-medium mb-2">Biographie</h4>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($user['documents_personnels']): ?>
                <div>
                    <h4 class="font-medium mb-2">Documents personnels</h4>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($user['documents_personnels'])) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!$user['domaine_recherche'] && !$user['bio'] && !$user['documents_personnels']): ?>
                <p class="text-muted text-center">Aucune information académique disponible</p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderInfoRow($label, $value)
    {
        ob_start();
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="font-medium"><?= htmlspecialchars($label) ?></span>
            <span class="text-muted"><?= htmlspecialchars($value) ?></span>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderRoleBadge($role)
    {
        $variants = [
            'admin' => 'destructive',
            'chercheur' => 'default',
            'enseignant' => 'secondary',
            'doctorant' => 'outline'
        ];
        
        return Components::Badge([
            'text' => ucfirst($role),
            'variant' => $variants[$role] ?? 'secondary'
        ]);
    }

    private function renderStatusBadge($status)
    {
        $variant = $status === 'actif' ? 'success' : 'destructive';
        
        return '<span style="margin-left: 0.5rem;">' . Components::Badge([
            'text' => ucfirst($status),
            'variant' => $variant
        ]) . '</span>';
    }
}
?>

<style>
    h4 {
        font-size: 0.875rem;
        font-weight: 500;
    }

    .mb-2 {
        margin-bottom: 0.5rem;
    }

    @media (max-width: 768px) {
        main > div[style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>