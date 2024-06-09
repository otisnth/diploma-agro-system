<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Orion\Http\Controllers\Controller;
use Orion\Http\Requests\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Policies\TruePolicy;

class UserController extends Controller
{
    protected $model = User::class;

    protected $policy = TruePolicy::class;

    public function filterableBy(): array
    {
        return ['post', 'name', 'email'];
    }

    public function sortableBy(): array
    {
        return ['name', 'email'];
    }

    protected function runIndexFetchQuery(Request $request, Builder $query, int $paginationLimit)
    {
        if ($request->limit == "all") {
            return $query->get();
        }
        else {
            return $this->shouldPaginate($request, $paginationLimit) ? $query->paginate($paginationLimit) : $query->get();
        }
    }

    protected function buildIndexFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildIndexFetchQuery($request, $requestedRelations);
        
        $query->where('id', '<>', Auth()->user()->id);

        return $query;
    }

}
