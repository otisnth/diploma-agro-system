<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Orion\Http\Controllers\Controller;
use Orion\Http\Requests\Request;
use Illuminate\Database\Eloquent\Builder;

class UserController extends Controller
{
    protected $model = User::class;

    public function filterableBy(): array
    {
        return ['post'];
    }

    public function sortableBy(): array
    {
        return ['name', 'email'];
    }

    protected function buildIndexFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildIndexFetchQuery($request, $requestedRelations);
        
        $query->where('id', '<>', Auth()->user()->id);

        return $query;
    }

}
