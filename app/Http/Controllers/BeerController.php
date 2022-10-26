<?php

namespace App\Http\Controllers;

use App\Exports\BeerExport;
use App\Http\Requests\BeerRequest;
use App\Jobs\ExportJob;
use App\Jobs\SendExportEmailJob;
use App\Jobs\StoreExportDataJob;
use App\Mail\ExportEmail;
use App\Models\Export;
use App\Models\Meal;
use App\Services\PunkapiServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class BeerController extends Controller
{
    public function index(BeerRequest $request, PunkapiServices $service)
    {
        //apesar de esta aparecendo que essta errado vai, pq meio vscode esta para php 7.4 não 8.1
        //return $service->getBeers(food:'cheese');
        $filters = $request->validated();
        $beers = $service->getBeers(...$filters);
        $meals = Meal::all();
        return Inertia::render('Beers', [
                'beers' => $beers,
                'meals' => $meals,
                'filters' => $filters
            ]
        );

    }
    public function export(BeerRequest $request, PunkapiServices $service)
    {

        //$filename = now()->timestamp."-export.xlsx";
        $filename = "cervejas-encontradas-".now()->format("Y-m-d H_i").".xlsx";

        ExportJob::withChain([
            new SendExportEmailJob($filename),
            new StoreExportDataJob(auth()->user(), $filename)
        ])->dispatch($request->validated(), $filename);

        return redirect()->back()
            ->with('success', 'Seu arquivo foi enviado para processamento e em breve estará em seu email');
    }
}
