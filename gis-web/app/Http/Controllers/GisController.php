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

    private function generateTigaData($countValue)
    {
        $tmp = [];
        do {
            for ($i = 0; $i < $countValue - 1; $i++) {
                $tmp[$i] = $this->generateRandomValue();
            }
            $tmp[$countValue - 1] = 1 - ($tmp[0] + $tmp[1]);
        } while ($tmp[0] <= 0 || $tmp[1] <= 0 || $tmp[2] <= 0);
        return $tmp;
    }


    private function originalFuzzyCmeans($data)
    {
        $dataCentroid = [];
        $totalLtSebelumnya = 0;
        $loop = 1;
        $bobot = 2;

        do {
            dump($loop);
            $dataMatrikPartisi = [];
            if ($loop == 1) {
                $countColumn = count($data[0]);
                for ($i = 0; $i < count($data); $i++) {
                    $tmp = $this->generateTigaData($countColumn);

                    $dataMatrikPartisi[] = $tmp;
                }
            } else {
                $matrikPartisiBaru = [];
                for ($i = 0; $i < count($data); $i++) {
                    $tmp = [];
                    for ($j = 0; $j < count($dataCentroid); $j++) {
                        $tmpNilai = 0;
                        for ($k = 0; $k < count($data[$i]); $k++) {
                            $tmpNilai += pow($data[$i][1] - $dataCentroid[$j][1], 2);
                        }
                        $tmp[] = pow($tmpNilai, (-1 / ($bobot - 1)));
                    }
                    $matrikPartisiBaru[] = $tmp;
                }
                for ($i = 0; $i < count($matrikPartisiBaru); $i++) {
                    $tmpNilai = 0;
                    for ($j = 0; $j < count($matrikPartisiBaru[$i]); $j++) {
                        $tmpNilai += $matrikPartisiBaru[$i][$j];
                    }
                    $tmp = [];
                    for ($k = 0; $k < count($matrikPartisiBaru[$i]); $k++) {
                        $tmp[] = $matrikPartisiBaru[$i][$k] / $tmpNilai;
                    }
                    $dataMatrikPartisi[] = $tmp;
                }
            }

            $dataCalonCentroid = [];
            $hasilJumlahUiPangkatBobot = [];
            $hasilJumlahUiBobotKaliXi = [];
            $kuadratDerajatKeanggotaan = [];

            for ($h = 0; $h < count($dataMatrikPartisi[0]); $h++) {
                $dataUiPangkatBobot = [];
                for ($j = 0; $j < count($dataMatrikPartisi); $j++) {
                    $tmp = pow($dataMatrikPartisi[$j][$h], $bobot);
                    $dataUiPangkatBobot[] = $tmp;
                }
                $kuadratDerajatKeanggotaan[] = $dataUiPangkatBobot;
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

            $resultJarakKaliUi = [];
            for ($j = 0; $j < count($dataCentroid); $j++) {
                $tmpL = [];
                for ($i = 0; $i < count($data); $i++) {
                    $tmp = 0;
                    for ($k = 0; $k < count($data[$i]); $k++) {
                        $tmp += pow($data[$i][$k] - $dataCentroid[$j][$k], 2);
                    }
                    $tmpL = $tmp * $kuadratDerajatKeanggotaan[$j][$i];
                }
                $resultJarakKaliUi[] = $tmpL;
            }

            $totalLt = 0;
            for ($i = 0; $i < count($resultJarakKaliUi[0]); $i++) {
                $sumLt = 0;
                for ($j = 0; $j < count($resultJarakKaliUi); $j++) {
                    $sumLt += $resultJarakKaliUi[$j][$i];
                }
                $totalLt += $sumLt;
            }
            dump($totalLt);
            $selisiFungsiObjectif = abs($totalLt - $totalLtSebelumnya);
            $totalLtSebelumnya = $totalLt;
            $loop += 1;
        } while ($selisiFungsiObjectif > 0.000001);
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
