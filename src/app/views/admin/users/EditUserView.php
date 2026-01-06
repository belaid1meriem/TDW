<?php
namespace App\Views\Admin\Users;
use App\Views\Admin\AdminLayout;
use Core\Components;

class EditUserView extends AdminLayout
{
    private $user;

    public function __construct($user)
    {
        parent::__construct();
        $this->user = $user;
    }

    public function renderContent()
    {
        ?>
        <main class="flex flex-col gap-6">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Modifier l'utilisateur</h2>
                <?= Components::Button([
                    'text' => '← Retour',
                    'variant' => 'outline',
                    'href' => '/admin/users'
                ]) ?>
            </div>

            <?php if ($this->hasFlash('error')): ?>
                <?= Components::Alert([
                    'variant' => 'destructive',
                    'message' => $this->flash('error'),
                    'dismissible' => true
                ]) ?>
            <?php endif; ?>

            <?= $this->renderUserForm() ?>
        </main>
        <?php
    }

    private function renderUserForm()
    {
        $user = $this->user;
        
        ob_start();
        ?>
        <form method="POST" action="/admin/users/update/<?= $user['id'] ?>" enctype="multipart/form-data">
            <?= Components::Card([
                'title' => 'Informations de connexion',
                'content' => $this->renderLoginSection()
            ]) ?>

            <?= Components::Card([
                'title' => 'Informations personnelles',
                'content' => $this->renderPersonalSection()
            ]) ?>

            <?= Components::Card([
                'title' => 'Informations professionnelles',
                'content' => $this->renderProfessionalSection()
            ]) ?>

            <?= Components::Card([
                'title' => 'Informations académiques',
                'content' => $this->renderAcademicSection()
            ]) ?>

            <div class="flex gap-4">
                <?= Components::Button([
                    'text' => 'Mettre à jour',
                    'type' => 'submit',
                    'variant' => 'default',
                    'class' => 'w-full'
                ]) ?>
                
                <?= Components::Button([
                    'text' => 'Annuler',
                    'type' => 'button',
                    'variant' => 'outline',
                    'class' => 'w-full',
                    'attrs' => ['onclick' => "window.location.href='/admin/users'"]
                ]) ?>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    private function renderLoginSection()
    {
        $user = $this->user;
        
        ob_start();
        ?>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <?= Components::Input([
                'type' => 'email',
                'name' => 'email',
                'label' => 'Email',
                'value' => $this->old('email', $user['email']),
                'error' => $this->hasError('email') ? $this->error('email') : '',
                'required' => true
            ]) ?>

            <?= Components::Input([
                'type' => 'text',
                'name' => 'username',
                'label' => 'Nom d\'utilisateur',
                'value' => $this->old('username', $user['username']),
                'error' => $this->hasError('username') ? $this->error('username') : '',
                'required' => true
            ]) ?>

            <?= Components::Input([
                'type' => 'password',
                'name' => 'password',
                'label' => 'Nouveau mot de passe',
                'placeholder' => 'Laisser vide pour ne pas changer',
                'error' => $this->hasError('password') ? $this->error('password') : ''
            ]) ?>

            <?= Components::Input([
                'type' => 'password',
                'name' => 'password_confirmation',
                'label' => 'Confirmer le mot de passe',
                'placeholder' => 'Confirmer le nouveau mot de passe',
                'error' => $this->hasError('password_confirmation') ? $this->error('password_confirmation') : ''
            ]) ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderPersonalSection()
    {
        $user = $this->user;
        
        ob_start();
        ?>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <?= Components::Input([
                'type' => 'text',
                'name' => 'first_name',
                'label' => 'Prénom',
                'value' => $this->old('first_name', $user['first_name']),
                'error' => $this->hasError('first_name') ? $this->error('first_name') : '',
                'required' => true
            ]) ?>

            <?= Components::Input([
                'type' => 'text',
                'name' => 'last_name',
                'label' => 'Nom',
                'value' => $this->old('last_name', $user['last_name']),
                'error' => $this->hasError('last_name') ? $this->error('last_name') : '',
                'required' => true
            ]) ?>

            <div style="grid-column: 1 / -1;">
                <?php if ($user['photo']): ?>
                    <div style="margin-bottom: 1rem;">
                        <img src="<?= $this->asset('images/' . $user['photo']) ?>" 
                             alt="Photo actuelle" 
                             style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
                    </div>
                <?php endif; ?>
                
                <?= Components::Input([
                    'type' => 'file',
                    'name' => 'photo',
                    'label' => 'Nouvelle photo de profil',
                    'error' => $this->hasError('photo') ? $this->error('photo') : '',
                    'attrs' => ['accept' => 'image/*']
                ]) ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderProfessionalSection()
    {
        $user = $this->user;
        
        ob_start();
        ?>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <?= Components::Select([
                'name' => 'role',
                'label' => 'Rôle',
                'options' => [
                    'admin' => 'Administrateur',
                    'enseignant' => 'Enseignant',
                    'chercheur' => 'Chercheur',
                    'doctorant' => 'Doctorant'
                ],
                'value' => $this->old('role', $user['role']),
                'error' => $this->hasError('role') ? $this->error('role') : '',
                'required' => true
            ]) ?>

            <?= Components::Input([
                'type' => 'text',
                'name' => 'grade',
                'label' => 'Grade',
                'value' => $this->old('grade', $user['grade']),
                'error' => $this->hasError('grade') ? $this->error('grade') : ''
            ]) ?>

            <?= Components::Input([
                'type' => 'text',
                'name' => 'poste',
                'label' => 'Poste',
                'value' => $this->old('poste', $user['poste']),
                'error' => $this->hasError('poste') ? $this->error('poste') : ''
            ]) ?>

            <?= Components::Select([
                'name' => 'status',
                'label' => 'Statut',
                'options' => [
                    'actif' => 'Actif',
                    'suspendu' => 'Suspendu',
                    'inactif' => 'Inactif'
                ],
                'value' => $this->old('status', $user['status']),
                'error' => $this->hasError('status') ? $this->error('status') : '',
                'required' => true
            ]) ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderAcademicSection()
    {
        $user = $this->user;
        
        ob_start();
        ?>
        <div style="display: grid; gap: 1rem;">
            <?= Components::Textarea([
                'name' => 'domaine_recherche',
                'label' => 'Domaine de recherche',
                'value' => $this->old('domaine_recherche', $user['domaine_recherche']),
                'error' => $this->hasError('domaine_recherche') ? $this->error('domaine_recherche') : '',
                'rows' => 3
            ]) ?>

            <?= Components::Textarea([
                'name' => 'bio',
                'label' => 'Biographie',
                'value' => $this->old('bio', $user['bio']),
                'error' => $this->hasError('bio') ? $this->error('bio') : '',
                'rows' => 4
            ]) ?>

            <?= Components::Textarea([
                'name' => 'documents_personnels',
                'label' => 'Documents personnels',
                'value' => $this->old('documents_personnels', $user['documents_personnels']),
                'error' => $this->hasError('documents_personnels') ? $this->error('documents_personnels') : '',
                'rows' => 3
            ]) ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
?>

<style>
    @media (max-width: 768px) {
        [style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>