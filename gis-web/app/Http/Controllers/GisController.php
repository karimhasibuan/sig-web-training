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

    private function dataCleaning($data)
    {
        // Create method for data cleaning. If 0 will be replaced with mean of column
        $countColumn = count($data[0]);
        for ($i = 0; $i < $countColumn; $i++) {
            $tmpAvg = 0;
            $tmpSum = 0;
            for ($j = 0; $j < count($data); $j++) {
                $tmpSum += $data[$j][$i];
            }
            $tmpAvg = $tmpSum / count($data);

            for ($k = 0; $k < count($data); $k++) {
                if ($data[$k][$i] == 0) {
                    $data[$k][$i] = $tmpAvg;
                }
            }
        }

        return $data;
    }

    private function zscore($data)
    {
        $dataResultZscore = [];
        $countColumn = count($data[0]);

        for ($i = 0; $i < $countColumn; $i++) {
            // AVG
            $tmpAvg = 0;
            $tmpSum = 0;
            for ($j = 0; $j < count($data); $j++) {
                $tmpSum = $tmpSum + $data[$j][$i];
            }
            $tmpAvg = $tmpSum / count($data);
            // STD
            $variance = 0.0;
            for ($j = 0; $j < count($data); $j++) {
                $variance += pow(($data[$j][$i] - $tmpAvg), 2);
            }
            $tmpStd = (float)sqrt($variance / count($data));
            // Zscore
            for ($k = 0; $k < count($data); $k++) {
                $tmpZscore = ($data[$k][$i] - $tmpAvg) / $tmpStd;
                $dataResultZscore[$k][$i] = $tmpZscore;
            }
        }
        return $dataResultZscore;
    }

    private function generateRandomValue()
    {
        return rand(0, 100) / 100;
    }

    private function generateDuaData($data)
    {
        $tmp = [];
        do {
            $tmp[0] = $this->generateRandomValue();
            $tmp[1] = 1 - $tmp[0];
        } while ($tmp[0] <= 0 || $tmp[1] <= 0);
        return $tmp;
    }

    private function generateTigaData($count)
    {
        $tmp = [];
        do {
            for ($i = 0; $i < $count - 1; $i++) {
                $tmp[$i] = $this->generateRandomValue();
            }
            $tmp[$count - 1] = 1 - ($tmp[0] + $tmp[1]);
        } while ($tmp[0] <= 0 || $tmp[1] <= 0 || $tmp[2] <= 0);
        return $tmp;
    }


    private function originalFuzzyCmeans($data)
    {
        $dataMatrikPartisi = [];
        $countColumn = count($data[0]);
        for ($i = 0; $i < count($data); $i++) {
            $tmp = $this->generateTigaData($countColumn);

            $dataMatrikPartisi[] = $tmp;
        }

        $bobot = 2;

        $dataCalonCentroid = [];
        $hasilJumlahUiPangkatBobot = [];
        $hasilJumlahUiBobotKaliXi = [];

        for ($h = 0; $h < count($dataMatrikPartisi[0]); $h++) {
            $dataUiPangkatBobot = [];
            for ($j = 0; $j < count($dataMatrikPartisi); $j++) {
                $tmp = pow($dataMatrikPartisi[$j][$h], $bobot);
                $dataUiPangkatBobot[] = $tmp;
            }
            $dataUiBobotKaliData = [];
            $jumlahUipangkatBobot = 0;
            $tmpJumlahUiBobotKaliXi = [0, 0, 0];
            for ($j = 0; $j < count($data); $j++) {
                $tmp = [];
                $jumlahUipangkatBobot += $dataUiPangkatBobot[$j];
                for ($k = 0; $k < count($data[$j]); $k++) {
                    $hasilKali = $data[$j][$k] * $dataUiPangkatBobot[$j];
                    $tmp[] = $hasilKali;
                    $tmpJumlahUiBobotKaliXi[$k] += $hasilKali;
                }
                $dataUiBobotKaliData[] = $tmp;
            }

            $hasilJumlahUiPangkatBobot[] = $jumlahUipangkatBobot;
            $hasilJumlahUiBobotKaliXi[] = $tmpJumlahUiBobotKaliXi;

            $dataCalonCentroid[] = $dataUiBobotKaliData;
        }

        dump($hasilJumlahUiPangkatBobot);
        dump($hasilJumlahUiBobotKaliXi);

        $dataCentroid = [];
        for ($i = 0; $i < count($hasilJumlahUiBobotKaliXi); $i++) {
            $tmp1 = [];
            for ($j = 0; $j < count($hasilJumlahUiBobotKaliXi[$i]); $j++) {
                $tmp2 = $hasilJumlahUiBobotKaliXi[$i][$j] / $hasilJumlahUiPangkatBobot[$j];
                $tmp1[] = $tmp2;
            }
            $dataCentroid[] = $tmp1;
        }
        dump($dataCentroid);
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

        // Data Cleaning
        $dataCleaning = $this->dataCleaning($dataSelection);

        // Normalisasi data
        $dataNormalisasi = $this->zscore($dataCleaning);

        // Fuzzy Cluster
        $dataHasilCluster = $this->originalFuzzyCmeans($dataNormalisasi);

        dd($dataHasilCluster);
    }
}
