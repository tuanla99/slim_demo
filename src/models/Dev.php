<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\PaginationState;

use Illuminate\Pagination\Paginator;

class Dev extends Model
{
    protected $table = 'devs';

    public function __construct()
    {
        parent::__construct();
    }

    public function getDev()
    {
        $sql = "SELECT * FROM dev";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }
}
