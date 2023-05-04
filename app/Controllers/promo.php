<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelpromo;
use App\Controllers\BaseController;

class promo extends BaseController
{
    use ResponseTrait;
    private $encrypt;
    private $bcrypt;
    public function __construct()
    {
        // Initialize the $encrypt property in the constructor
        $this->encrypt = \Config\Services::encrypter();
        $this->bcrypt = \Config\Services::bcrypt();
    }
    // public function index()
    // {
    //     $Modelpromo = new Modelpromo();
    //     $data = $Modelpromo->findAll();
    //     $response = [
    //         'status' => 200,
    //         'error' => "false",
    //         'message' => '',
    //         'totaldata' => count($data),
    //         'data' => $data,
    //     ];
    //     return $this->respond($response, 200);
    // }
    public function index()
    {
        $encryption = \Config\Services::encrypter();
        $Modelpromo = new Modelpromo();
        $data = $Modelpromo->findAll();

        // foreach ($data as &$row) {
        //     $row['password'] = $encryption->decrypt(hex2bin($row['password']));
        // }

        $response = [
            'status' => 200,
            'error' => "false",
            'message' => '',
            'totaldata' => count($data),
            'data' => $data,
        ];

        return $this->respond($response, 200);
    }

    public function show($jenis=null)
    {
        // if($category == "forgot"){
            $Modelpromo = new Modelpromo();
            $data = $Modelpromo->where('jenis_promo', $jenis)->get()->getResult();
            if (count($data) > 0) { // Update condition to check if data is not empty
                $response = [
                    'status' => 200,
                    'error' => false,
                    'message' => '',
                    'totaldata' => count($data),
                    'data' => $data, // Return $data directly
                ];
                return $this->respond($response, 200);
            } else {
                return $this->failNotFound('maaf data ' . $jenis . ' tidak ditemukan');
            }
        // } else {
            // $encryption = \Config\Services::encrypter();
            // $Modelpromo = new Modelpromo();
            // $data = $Modelpromo->where('nama_promo', $nama)->get()->getRow();
            // $passworddecrypt = $encryption->decrypt(hex2bin($data->password));
            // if ($data && $password == $passworddecrypt) {
            //     $response = [
            //         'status' => 200,
            //         'error' => false,
            //         'message' => '',
            //         'totaldata' => 1,
            //         'data' => $data, // Wrap $data in an array
            //     ];
            //     return $this->respond($response, 200);
            // } else {
            //     return $this->failNotFound('Maaf, data ' . $nama . ' tidak ditemukan atau password salah');
            // }
        // }
    }
    

    public function create()
    {
        $Modelpromo = new Modelpromo();
        $nama_promo = $this->request->getPost("nama_promo");
        $encryption= \Config\Services::encrypter();
        $password = bin2hex($encryption->encrypt($this->request->getPost("password"))); 
        // Generate Bcrypt hash of the password
        $umur = $this->request->getPost("umur");
        $no_telp = $this->request->getPost("no_telp");
        $jabatan = $this->request->getPost("jabatan");
        // $validation = \Config\Services::validation();
        // $valid = $this->validate([
        //     'nama' => [
        //         'rules' => 'is_unique[akun.nama]',
        //         'label' => 'nama Akun',
        //         'errors' => [
        //             'is_unique' => "Akun {field} sudah ada"
        //         ]
        //     ]
        // ]);
        // if (!$valid) {
        //     $response = [
        //         'status' => 404,
        //         'error' => true,
        //         'message' => $validation->getError("nama"),
        //     ];
        //     return $this->respond($response, 404);
        // } else {
            $Modelpromo->insert([
                'nama_promo' => $nama_promo,
                'password'=> $password,
                'umur' => $umur,
                'no_telp' => $no_telp,
                'jabatan' => $jabatan
            ]);
            $response = [
                'status' => 201,
                'error' => "false",
                'message' => "Register Berhasil"
            ];
            return $this->respond($response, 201);
        // }
    }

    public function update($nama = null,$status = null)
    {
        if($status == "verifikasi"){
            $model = new Modelpromo();
            $data = [
                'nama' => $this->request->getVar("nama"),
                'password' => $this->request->getVar("password"),
                'email' => $this->request->getVar("email"),
                'no_telp' => $this->request->getVar("no_telp"),
                'deposit_uang' => $this->request->getVar("deposit_uang"),
                'deposit_kelas' => $this->request->getVar("deposit_kelas"),
                'Expiration_Date' => $this->request->getVar("Expiration_Date"),
                'status' => $this->request->getVar("status")
            ];
            $data = $this->request->getRawInput();
            $model->update($nama, $data);
            $response = [
                'status' => 200,
                'error' => null,
                'message' => "Akun $nama berhasil diupdate"
            ];
            return $this->respond($response, 201);
        }else{
            $model = new Modelpromo();
            $data = [
                'nama' => $this->request->getVar("nama"),
                'password' => $this->request->getVar("password"),
                'email' => $this->request->getVar("email"),
                'no_telp' => $this->request->getVar("no_telp"),
                'deposit_uang' => $this->request->getVar("deposit_uang"),
                'deposit_kelas' => $this->request->getVar("deposit_kelas"),
                'Expiration_Date' => $this->request->getVar("Expiration_Date"),
                'status' => $this->request->getVar("status")
            ];
            $validation = \Config\Services::validation();
            // $valid = $this->validate([
            //     'nama' => [
            //         'rules' => 'is_unique[akun.nama]',
            //         'label' => 'nama Akun',
            //         'errors' => [
            //             'is_unique' => "Akun {field} sudah ada"
            //         ]
            //     ]
            // ]);
            // if (!$valid) {
            //     $response = [
            //         'status' => 404,
            //         'error' => true,
            //         'message' => $validation->getError("nama"),
            //     ];
            //     return $this->respond($response, 404);
            // } else {
                $data = $this->request->getRawInput();
                $model->update($nama, $data);
                $response = [
                    'status' => 200,
                    'error' => null,
                    'message' => "Akun $nama berhasil diupdate"
                ];
                return $this->respond($response, 201);
            // }
        }
        
    }

    public function delete($nama)
    {
        $Modelpromo = new Modelpromo();
        $cekData = $Modelpromo->find($nama);
        if ($cekData) {
            $Modelpromo->delete($nama);
            $response = [
                'status' => 200,
                'error' => null,
                'message' => "Selamat data sudah berhasil dihapus maksimal"
            ];
            return $this->respondDeleted($response);
        } else {
            return $this->failNotFound('Data tidak ditemukan kembali');
        }
    }
}
