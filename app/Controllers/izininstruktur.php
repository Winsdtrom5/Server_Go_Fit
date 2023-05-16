<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelizininstruktur;
use App\Controllers\BaseController;
use App\Models\Modelinstruktur;
use DateTime;
use IntlDateFormatter;

class izininstruktur extends BaseController
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
        $Modelizininstruktur = new Modelizininstruktur();
        $data = $Modelizininstruktur->select('izininstruktur.*, jadwalharian.tanggal_kelas, jadwalumum.hari, jadwalumum.jam, instruktur1.nama , kelas.nama_kelas')
            ->join('jadwalharian', 'izininstruktur.id_jadwal = jadwalharian.id')
            ->join('jadwalumum', 'jadwalharian.jadwal = jadwalumum.id')
            ->join('instruktur as instruktur1', 'jadwalumum.id_instruktur = instruktur1.id_instruktur')
            ->join('kelas', 'jadwalumum.id_kelas = kelas.id_kelas')
            ->findAll();

        foreach ($data as &$row) {
            // Modify the date format
            $date = new DateTime($row['tanggal_kelas']);
            $datestring = $date->format('d M Y');
            $datestring = str_replace(
                ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                $datestring
            );
            $row['tanggal_izin'] = $datestring;
            $row['tanggal_kelas'] = $datestring;
            unset($row['id_instruktur'], $row['id_kelas']);
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

    public function show($nama = null,$category = null)
    {
        if($category == null){
            $Modelizininstruktur = new Modelizininstruktur();
            $data = $Modelizininstruktur->where('nama', $nama)->get()->getResult();
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
            $Modelizininstruktur = new Modelizininstruktur();
            $data = $Modelizininstruktur->where('email', $nama)->get()->getRow();
            $passworddecrypt = $encryption->decrypt(hex2bin($data->password));
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
    

    public function create()
    {
        $Modelizininstruktur = new Modelizininstruktur();
        $nama_instruktur = $this->request->getPost("nama_instruktur");
        $Modelinstruktur = new Modelinstruktur();
        $instruktur = $Modelinstruktur->where('nama', $nama_instruktur)->first();
        // Generate Bcrypt hash of the password
        if ($instruktur === null) {
            $response = [
                'status' => 200,
                'error' => "false",
                'message' => 'Gagal',
            ];
            return $this->respond($response, 200);
        }
        $id_instruktur = $instruktur['id_instruktur'];
        $status = $this->request->getPost("status");
        $alasan = $this->request->getPost("alasan");
        $tanggal_izin = $this->request->getPost("tanggal_izin");
            $Modelizininstruktur->insert([
                'id_instruktur' => $id_instruktur,
                'password'=> $status,
                'alasan' => $alasan,
                'tanggal_izin' => $tanggal_izin
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
        $model = new Modelizininstruktur();
        $data = $this->request->getJSON(true);
        // Update only non-empty values
        $data['status'] = 'Confirmed';
        $model->update($id, $data);
        $response = [
            'status' => 200,
            'error' => null,
            'message' => $data,
        ];
        return $this->respond($response, 201);
    }


    public function delete($id)
    {
        $Modelizininstruktur = new Modelizininstruktur();
        $cekData = $Modelizininstruktur->find($id);
        if ($cekData) {
            $Modelizininstruktur->delete($id);
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