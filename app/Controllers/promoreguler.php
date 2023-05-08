<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelpromoreguler;
use App\Controllers\BaseController;

class promoreguler extends BaseController
{
    use ResponseTrait;
    private $encrypt;
    private $bcrypt;


    public function index()
    {
        $encryption = \Config\Services::encrypter();
        $Modelpromoreguler = new Modelpromoreguler();
        $data = $Modelpromoreguler->findAll();

        $response = [
            'status' => 200,
            'error' => "false",
            'message' => '',
            'totaldata' => count($data),
            'data' => $data,
        ];

        return $this->respond($response, 200);
    }

    public function show($minimal = null,$requirement = null)
    {
        $encryption = \Config\Services::encrypter();
        $Modelpromoreguler = new Modelpromoreguler();
        $data = $Modelpromoreguler->where('minimal', $minimal)->get()->getRow();
        if ($data && $requirement > $data->requirement) {
            $response = [
                'status' => 200,
                'error' => false,
                'message' => '',
                'totaldata' => 1,
                'data' => $data, // Wrap $data in an array
            ];
            return $this->respond($response, 200);
        } else {
            return $this->failNotFound('Maaf, data ' . $minimal. ' tidak ditemukan atau password salah');
        }
    }
}