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

    private function zscore($data)
    {
        $countColumn = count($data[0]);

        for ($i = 0; $i < $countColumn; $i++) {
            $sum = 0;
            for ($j = 0; $j < count($data); $j++) {
                $sum += $data[$j][$i];
            }
            $mean = $sum / count($data);

            $sum = 0;
            for ($j = 0; $j < count($data); $j++) {
                $sum += pow($data[$j][$i] - $mean, 2);
            }
            $std = sqrt($sum / count($data));

            for ($j = 0; $j < count($data); $j++) {
                $data[$j][$i] = ($data[$j][$i] - $mean) / $std;
            }
        }
        return $data;
    }

    public function fuzzycmeans()
    {
        $dataCovid = DB::table('data_covid')->get();

        $dataSelection = [];
        for ($i = 0; $i < count($dataCovid); $i++) {
            $dataSelection[] =
                [
                    floatval($dataCovid[$i]->konfirmasi),
                    floatval($dataCovid[$i]->sembuh),
                    floatval($dataCovid[$i]->meninggal)
                ];
        }

        // dd($dataSelection);

        // Normalisasi data
        $dataNormalisasi = $this->zscore($dataSelection);

        dd($dataNormalisasi);
    }
}
