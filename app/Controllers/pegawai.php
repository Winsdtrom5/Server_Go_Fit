<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelpegawai;
use App\Controllers\BaseController;
use App\Models\Modelinstruktur;

class pegawai extends BaseController
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
    public function index()
    {
        $encryption = \Config\Services::encrypter();
        $Modelpegawai = new Modelpegawai();
        $data = $Modelpegawai->findAll();

        foreach ($data as &$row) {
            $row['password'] = $encryption->decrypt(hex2bin($row['password']));
        }

        $response = [
            'status' => 200,
            'error' => "false",
            'message' => '',
            'totaldata' => count($data),
            'data' => $data,
        ];

        return $this->respond($response, 200);
    }

    public function show($nama = null,$password = null,$category = null)
    {
        if($category == "forgot"){
            $Modelpegawai = new Modelpegawai();
            $data = $Modelpegawai->where('email', $nama)->get()->getResult();
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
                return $this->failNotFound('maaf data ' . $nama . ' tidak ditemukan');
            }
        } else {
            $encryption = \Config\Services::encrypter();
            $Modelpegawai = new Modelpegawai();
            $data = $Modelpegawai->where('email', $nama)->get()->getRow();
            $passworddecrypt = $encryption->decrypt(hex2bin($data->password));
            if ($data && $password == $passworddecrypt) {
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
    

    public function create()
    {
        $Modelpegawai = new Modelpegawai();
        $nama_pegawai = $this->request->getPost("nama_pegawai");
        $encryption= \Config\Services::encrypter();
        $password = bin2hex($encryption->encrypt($this->request->getPost("password"))); 
        // Generate Bcrypt hash of the password
        $umur = $this->request->getPost("umur");
        $no_telp = $this->request->getPost("no_telp");
        $jabatan = $this->request->getPost("jabatan");
            $Modelpegawai->insert([
                'nama_pegawai' => $nama_pegawai,
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

    public function update($id = null)
    {
        $model = new Modelinstruktur();
        $data = $this->request->getJSON(true);
        // Update only non-empty values
        if (!empty($this->request->getVar("nama_member"))) {
            $data['nama_member'] = $this->request->getVar("nama_member");
        }
        if (array_key_exists('password', $data)) {
            // Encrypt the password
            $encryption = \Config\Services::encrypter();
            $data['password'] = bin2hex($encryption->encrypt($data['password']));
            $response = [
                'status' => 200,
                'error' => null,
                'message' => "Aman"
            ];
        } else {
            $response = [
                'status' => 200,
                'error' => 'PasswordRequired',
                'message' => "Password is required"
            ];
        }        
        if (!empty($this->request->getVar("umur"))) {
            $data['umur'] = $this->request->getVar("umur");
        }
        if (!empty($this->request->getVar("no_telp"))) {
            $data['no_telp'] = $this->request->getVar("no_telp");
        }
        $model->update($id, $data);
        return $this->respond($response, 201);
    }


    public function delete($nama)
    {
        $Modelpegawai = new Modelpegawai();
        $cekData = $Modelpegawai->find($nama);
        if ($cekData) {
            $Modelpegawai->delete($nama);
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