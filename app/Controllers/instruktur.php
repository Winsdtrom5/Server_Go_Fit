<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelinstruktur;
use App\Controllers\BaseController;

class instruktur extends BaseController
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
    //     $Modelinstruktur = new Modelinstruktur();
    //     $data = $Modelinstruktur->findAll();
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
        $Modelinstruktur = new Modelinstruktur();
        $data = $Modelinstruktur->findAll();
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

    public function show($nama = null, $password = null, $category = null)
    {
        if ($category == "forgot") {
            $Modelinstruktur = new Modelinstruktur();
            $data = $Modelinstruktur->where('nama', $nama)->get()->getResult();
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
        } else {
            $encryption = \Config\Services::encrypter();
            $Modelinstruktur = new Modelinstruktur();
            $data = $Modelinstruktur->where('nama', $nama)->get()->getRow();
            if ($data && password_verify($password, $data['password'])) {
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


    public function create()
    {
        $Modelinstruktur = new Modelinstruktur();
        $nama = $this->request->getPost("nama");
        $encryption = \Config\Services::encrypter();
        $password = bin2hex($encryption->encrypt($this->request->getPost("password")));
        // Generate Bcrypt hash of the password
        $umur = $this->request->getPost("umur");
        $no_telp = $this->request->getPost("no_telp");
        $Modelinstruktur->insert([
            'nama' => $nama,
            'password' => $password,
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

    public function update($id_instruktur = null)
    {
        if ($id_instruktur == "reset") {
            $model = new Modelinstruktur();
            $data = ['keterlambatan' => '00:00:00'];
            $model->where('1=1')->set($data)->update(); // Update all rows in the table
            $response = [
                'status' => 200,
                'error' => null,
                'message' => $data['keterlambatan'],
            ];
            return $this->respond($response, 201);        
        } else {
            $model = new Modelinstruktur();
            $data = $this->request->getJSON(true);
            // Update only non-empty values
            if (!empty($this->request->getVar("nama"))) {
                $data['nama_member'] = $this->request->getVar("nama");
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
            $model->update($id_instruktur, $data);
            return $this->respond($response, 201);
        }
    }

    public function delete($nama)
    {
        $Modelinstruktur = new Modelinstruktur();
        $cekData = $Modelinstruktur->find($nama);
        if ($cekData) {
            $Modelinstruktur->delete($nama);
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
