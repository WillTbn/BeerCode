<?php

namespace App\Http\Controllers;

use App\Exports\BeerExport;
use App\Http\Requests\BeerRequest;
use App\Jobs\ExportJob;
use App\Jobs\SendExportEmailJob;
use App\Jobs\StoreExportDataJob;
use App\Mail\ExportEmail;
use App\Models\Export;
use App\Services\PunkapiServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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

        //$filename = now()->timestamp."-export.xlsx";
        $filename = "cervejas-encontradas-".now()->format("Y-m-d H_i").".xlsx";

        ExportJob::withChain([
            new SendExportEmailJob($filename),
            new StoreExportDataJob(auth()->user(), $filename)
        ])->dispatch($request->validated(), $filename);

        return 'Relatorio criado!';
    }
}
