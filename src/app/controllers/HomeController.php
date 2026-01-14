<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\ActualitesModel;
use App\Models\PublicationsModel;
use App\Models\EvenementsModel;
use App\Models\ProjetsModel;
use App\Models\PartnersModel;
use App\Models\ConfigurationsModel;
use App\Models\EquipesModel;
use App\Models\UsersModel;
use App\Views\User\HomeView;

class HomeController extends Controller
{
    private ActualitesModel $actualitesModel;
    private PublicationsModel $publicationsModel;
    private EvenementsModel $evenementsModel;
    private ProjetsModel $projetsModel;
    private PartnersModel $partnersModel;
    private ConfigurationsModel $configModel;
    private EquipesModel $equipesModel;
    private UsersModel $usersModel;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->actualitesModel = new ActualitesModel();
        $this->publicationsModel = new PublicationsModel();
        $this->evenementsModel = new EvenementsModel();
        $this->projetsModel = new ProjetsModel();
        $this->partnersModel = new PartnersModel();
        $this->configModel = new ConfigurationsModel();
        $this->equipesModel = new EquipesModel();
        $this->usersModel = new UsersModel();
    }

    /**
     * Display homepage with all sections
     */
    public function index()
    {
        // Get page number for events pagination
        $eventsPage = (int) ($this->request->query('events_page') ?? 1);

        // Aggregate homepage data
        $data = [
            'slideshow' => $this->actualitesModel->getLatest(5),
            'labInfo' => $this->configModel->get(),
            'publications' => $this->publicationsModel->getRecent(6),
            'events' => $this->evenementsModel->getUpcoming($eventsPage, 6),
            'projects' => $this->projetsModel->getActive(4),
            'partners' => $this->partnersModel->getAll(),
            'teams' => $this->equipesModel->getAll(),
            'stats' => [
                'publications' => $this->publicationsModel->countValidated(),
                'projects' => $this->projetsModel->countActive(),
                'teams' => $this->equipesModel->countAll()
            ]
        ];

        // Render the homepage view
        $view = new HomeView($data);
        $this->render($view);
    }
}
