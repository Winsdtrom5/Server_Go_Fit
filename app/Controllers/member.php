<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelmember;
use App\Controllers\BaseController;

class member extends BaseController
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
    //     $Modelmember = new Modelmember();
    //     $data = $Modelmember->findAll();
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
        $Modelmember = new Modelmember();
        $data = $Modelmember->findAll();

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
        if($category == "searchmember"){
            $Modelmember = new Modelmember();
            $data = $Modelmember->where('nama_member', $nama)->get()->getResult();
            if (count($data) > 1) {
                $response = [
                    'status' => 200,
                    'error' => "false",
                    'message' => '',
                    'totaldata' => count($data),
                    'data' => $data,
                ];
                return $this->respond($response, 200);
            } else if (count($data) == 1) {
                $response = [
                    'status' => 200,
                    'error' => "false",
                    'message' => '',
                    'totaldata' => count($data),
                    'data' => $data,
                ];
                return $this->respond($response, 200);
            } else {
                return $this->failNotFound('maaf data ' . $nama .
                    ' tidak ditemukan');
            }
        }else{
            $encryption = \Config\Services::encrypter();
            $Modelmember = new Modelmember();
            $data = $Modelmember->where('nama_member', $nama)->get()->getRow();
            $passworddecrypt = $encryption->decrypt(hex2bin($data->password));
            if ($data && $password == $passworddecrypt) {
                // $encryption->decrypt(hex2bin($data->tanggal_lahir)
                $response = [
                    'status' => 200,
                    'error' => false,
                    'message' => '',
                    'totaldata' => 1,
                    'data' => $data,
                ];
                return $this->respond($response, 200);
            } else {
                return $this->failNotFound('Maaf, data ' . $nama . ' tidak ditemukan atau password salah');
            }
        }
    }
    
    public function showmember($id = null)
    {
        $Modelmember = new Modelmember();
        $data = $Modelmember->find($id);
        if ($data) {
            $response = [
                'status' => 200,
                'error' => false,
                'message' => '',
                'totaldata' => 1,
                'data' => $data,
            ];
            return $this->respond($response, 200);
        } else {
            return $this->failNotFound('Maaf, data tidak ditemukan');
        }
    }


    public function create()
    {
        $Modelmember = new Modelmember();
        $nama_member = $this->request->getPost("nama_member");
        $umur = $this->request->getPost("umur");
        $encryption= \Config\Services::encrypter();
        $password = bin2hex($encryption->encrypt($this->request->getPost("password"))); 
        $tanggal_lahir = $this->request->getPost("tanggal_lahir");
        $email = $this->request->getPost("email");
        $no_telp = $this->request->getPost("no_telp");
        $date_daftar = $this->request->getPost("date_daftar");
        $deposit_uang = $this->request->getPost("deposit_uang");
        $deposit_kelas = $this->request->getPost("deposit_kelas");
        $Expiration_Date = $this->request->getPost("Expiration_Date");
        $status = $this->request->getPost("status");
            $Modelmember->insert([
                'nama_member' => $nama_member,
                'password' => $password,
                'tanggal_lahir'=> $tanggal_lahir,
                'umur' => $umur,
                'email' => $email,
                'no_telp' => $no_telp,
                'date_daftar' => $date_daftar,
                'deposit_uang' => $deposit_uang,
                'deposit_kelas' => $deposit_kelas,
                'Expiration_Date' => $Expiration_Date,
                'status' => $status
            ]);
            $response = [
                'status' => 201,
                'error' => "false",
                'message' => "Register Berhasil"
            ];
            return $this->respond($response, 201);
        // }
    }

    public function update($id_member = null,$status = null)
    {
        $model = new Modelmember();
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
        if (!empty($this->request->getVar("email"))) {
            $data['email'] = $this->request->getVar("email");
        }
        if (!empty($this->request->getVar("tanggal_lahir"))) {
            $data['tanggal_lahir'] = $this->request->getVar("tanggal_lahir");
        }
        if (!empty($this->request->getVar("deposit_uang"))) {
            $data['deposit_uang'] = $this->request->getVar("deposit_uang");
        }
        if (!empty($this->request->getVar("deposit_kelas"))) {
            $data['deposit_kelas'] = $this->request->getVar("deposit_kelas");
        }
        if (!empty($this->request->getVar("Expiration_Date"))) {
            $data['Expiration_Date'] = $this->request->getVar("Expiration_Date");
        }
        if (!empty($this->request->getVar("status"))) {
            $data['status'] = $this->request->getVar("status");
        }
        $model->update($id_member, $data);
        return $this->respond($response, 201);
    }

    public function delete($id_member)
    {
        $Modelmember = new Modelmember();
        $cekData = $Modelmember->find($id_member);
        if ($cekData) {
            $Modelmember->delete($id_member);
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
