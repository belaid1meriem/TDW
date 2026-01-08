<?php
namespace App\Models;
use Core\Model;

class PublicationModel extends Model
{
    protected string $table = 'publications';

    public function getValidatedPublications()
    {
        return $this->select(['status' => 'validated']);
    }

    public function getPendingPublications()
    {
        return $this->select(['status' => 'pending']);
    }

    public function getRejectedPublications()
    {
        return $this->select(['status' => 'rejected']);
    }

    public function getPublicationsByAuthor($authorId)
    {
        return $this->select(['author_id' => $authorId]);
    }   

    public function getPublicationsByYear($year)
    {
        return $this->select(['YEAR(publication_date)' => $year]);
    }

    public function updatePublicationStatus($id, $status)
    {
        return $this->update(['status' => $status], ['id' => $id]);
    }


}