<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelkelas;
use App\Controllers\BaseController;

class kelas extends BaseController
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
        $Modelkelas = new Modelkelas();
        $data = $Modelkelas->findAll();

        $response = [
            'status' => 200,
            'error' => "false",
            'message' => '',
            'totaldata' => count($data),
            'data' => $data,
        ];

        return $this->respond($response, 200);
    }

    public function show($nama_kelas = null,$category = null)
    {
        $Modelkelas = new Modelkelas();
        $data = $Modelkelas->where('nama_kelas', $nama_kelas)->get()->getResult();
        if (count($data) >= 1) {
            $response = [
                'status' => 200,
                'error' => "false",
                'message' => '',
                'totaldata' => count($data),
                'data' => $data,
            ];
             return $this->respond($response, 200);
        } else {
            return $this->failNotFound('maaf data ' . $nama_kelas .
                ' tidak ditemukan');
        }
    }
    

    public function create()
    {
        $Modelkelas = new Modelkelas();
        $nama = $this->request->getPost("nama");
        $encryption= \Config\Services::encrypter();
        $password = bin2hex($encryption->encrypt($this->request->getPost("password"))); 
        // Generate Bcrypt hash of the password
        $umur = $this->request->getPost("umur");
        $no_telp = $this->request->getPost("no_telp");
        $Modelkelas->insert([
            'nama' => $nama,
            'password'=> $password,
            'umur' => $umur,
            'no_telp' => $no_telp
        ]);
        $response = [
            'status' => 201,
            'error' => "false",
            'message' => "Register Berhasil"
        ];
        return $this->respond($response, 201);
    }

    public function update($id_kelas = null)
    {
        $model = new Modelkelas();
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
        $model->update($id_kelas, $data);
        return $this->respond($response, 201);
    }

    public function delete($nama)
    {
        $Modelkelas = new Modelkelas();
        $cekData = $Modelkelas->find($nama);
        if ($cekData) {
            $Modelkelas->delete($nama);
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
