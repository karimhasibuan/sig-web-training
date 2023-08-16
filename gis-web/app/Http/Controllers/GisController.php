<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GisController extends Controller
{
    public function index()
    {
        $fileNames = [];
        $path = public_path('geojson');
        $files = \File::allFiles($path);

        foreach ($files as $file) {
            array_push($fileNames, pathinfo($file)['filename'] . '.geojson');
        }

        $jsonFileName = json_encode($fileNames);

        return view('beranda', compact('jsonFileName'));
    }
}
