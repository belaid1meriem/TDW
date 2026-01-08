<?php
namespace App\Views\Admin\Publications;

use App\Views\Admin\AdminLayout;
use Core\Components;
use App\Views\Components\PublicationsTable;


class PublicationsView extends AdminLayout
{
    private $validatedPublications;
    private $pendingPublications;
    private $rejectedPublications;
    private $publicationsTable;
    

    public function __construct($validatedPublications = [], $pendingPublications = [], $rejectedPublications = [])
    {
        parent::__construct();

        $this->validatedPublications = $validatedPublications;
        $this->pendingPublications = $pendingPublications;
        $this->rejectedPublications = $rejectedPublications;
        $this->publicationsTable = new PublicationsTable();
    }

    public function renderContent()
    {
        ?>
        <main class="flex flex-col gap-6">

            <?php if ($this->hasFlash('success')): ?>
                <div class="alert-container">
                    <?= Components::Alert([
                        'variant' => 'success',
                        'message' => $this->flash('success'),
                        'dismissible' => true
                    ]) ?>
                </div>
            <?php endif; ?>

        
            <h2>Gestion des publications</h2>

            <h3>Publications en attente</h3>
            <?= $this->publicationsTable->renderPublicationList($this->pendingPublications); ?> 
            
            <h3>Publications validées</h3>
            <?= $this->publicationsTable->renderPublicationList($this->validatedPublications);?>

            <h3>Publications rejetées</h3>
            <?= $this->publicationsTable->renderPublicationList($this->rejectedPublications); ?>

        </main>
        <?php
    }

}
