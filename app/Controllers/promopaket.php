<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelpromopaket;
use App\Controllers\BaseController;

class promopaket extends BaseController
{
    use ResponseTrait;
    private $encrypt;
    private $bcrypt;


    public function index()
    {
        $encryption = \Config\Services::encrypter();
        $Modelpromopaket = new Modelpromopaket();
        $data = $Modelpromopaket->findAll();

        $response = [
            'status' => 200,
            'error' => "false",
            'message' => '',
            'totaldata' => count($data),
            'data' => $data,
        ];

        return $this->respond($response, 200);
    }

    public function show($nama = null,$requirement = null)
    {
        $encryption = \Config\Services::encrypter();
        $Modelpromopaket = new Modelpromopaket();
        $data = $Modelpromopaket->where('nama_promo', $nama)->where('requirement',$requirement)->get()->getRow();
        if ($data) {
            $response = [
                'status' => 200,
                'error' => false,
                'message' => '',
                'totaldata' => 1,
                'data' => $data, // Wrap $data in an array
            ];
            return $this->respond($response, 200);
        } else {
            return $this->failNotFound('Maaf, data ' . $nama . ' tidak ditemukan atau password salah');
        }
    }
}