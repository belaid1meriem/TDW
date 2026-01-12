<?php
namespace App\Models;

use Core\Model;
use Core\AutoCrud\CrudEngine;
use Core\AutoCrud\VirtualModel;
/**
 * EvenementsModel - Handles events
 */
class EvenementsModel extends Model
{
    protected function define(): VirtualModel
    {
        return VirtualModel::fromTable('evenements');
    }

    public function getUpcoming(int $page = 1, int $perPage = 6): array
    {
        $crud = new CrudEngine($this->define());
        $today = date('Y-m-d');
        $result = $crud->list([], $page, $perPage, 'event_date ASC');

        $upcoming = array_filter($result['data'], fn($e) => $e['event_date'] >= $today);
        $upcoming = array_values($upcoming);

        return [
            'data' => array_slice($upcoming, 0, $perPage),
            'total' => count($upcoming),
            'page' => $page,
            'perPage' => $perPage,
            'pages' => ceil(count($upcoming) / $perPage)
        ];
    }
}
