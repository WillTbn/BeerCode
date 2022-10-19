<?php

namespace App\Http\Controllers;

use App\Exports\BeerExport;
use App\Http\Requests\BeerRequest;
use App\Services\PunkapiServices;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BeerController extends Controller
{
    public function index(BeerRequest $request, PunkapiServices $service)
    {
        $params = $request->validated();
        //apesar de esta aparecendo que essta errado vai, pq meio vscode esta para php 7.4 não 8.1 
        //return $service->getBeers(food:'cheese');
        return $service->getBeers(...$params);

    }
    public function export(BeerRequest $request, PunkapiServices $service)
    {
        $beers = $service->getBeers(...$request->validated());

        $filteredBeers = collect($beers)->map(function($value, $key){
           return collect($value)
           ->only(['name', 'tagline', 'first_brewed', 'description'])
           ->toArray();
        })->toArray();;
        //dd($filteredBeers);
        Excel::store(new BeerExport($filteredBeers), 'olw-report.xlsx', 's3');
        return 'Relatorio criado!';
    }
}
