<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GisController extends Controller
{
    public function index()
    {
        // code below for get data JSON from files inside folder
        // $fileNames = [];
        // $path = public_path('geojson');
        // $files = \File::allFiles($path);

        // foreach ($files as $file) {
        //     array_push($fileNames, pathinfo($file)['filename'] . '.geojson');
        // }

        // $jsonFileName = json_encode($fileNames);
        $dataSumut = DB::table('data_covid')->get();

        $dataJsonSumut = json_encode($dataSumut);

        return view('beranda', compact('dataJsonSumut'));
    }
}
