<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Search;
use App\Helpers\Response;
use App\Core\Controller;

class SearchController extends Controller
{
    private Search $search;

    public function __construct()
    {
        $this->search = new Search();
    }

    public function index(): void
    {
        $query = trim((string)($_GET['q'] ?? ''));

        if ($query === '') {
            $this->error('Search query is required.', 400);
            return;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));

        $result = $this->search->search($query, $page);

        $this->success($result);
    }
}