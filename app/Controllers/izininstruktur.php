<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelizininstruktur;
use App\Models\Modeljadwalharian;
use App\Controllers\BaseController;
use App\Models\Modeljadwalumum;
use App\Models\Modelinstruktur;
use App\Models\Modelkelas;
use DateTime;

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

    public function show($nama = null, $category = null)
    {
        if ($category == null) {
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
        $nama_kelas = $this->request->getPost("nama_kelas");
        $nama =  $this->request->getPost("nama");
        $tanggal = $this->request->getPost("tanggal");
        $jam = $this->request->getPost("jam");
        $instruktur_pengganti =  $this->request->getPost("instruktur_pengganti");
        $status = $this->request->getPost("status");
        $alasan = $this->request->getPost("alasan");
        $tanggal_izin = $this->request->getPost("tanggal_izin");
        $Modelinstruktur = new Modelinstruktur();
        $instrukturpengganti = $Modelinstruktur->where('nama', $instruktur_pengganti)->first();
        $instruktur= $Modelinstruktur->where('nama', $nama)->first();
        if ($instruktur === null) {
            $response = [
                'status' => 200,
                'error' => "true",
                'message' => 'Gagal, instruktur tidak ditemukan.',
            ];
            return $this->respond($response, 200);
        }
        if ($instrukturpengganti === null) {
            $response = [
                'status' => 200,
                'error' => "true",
                'message' => 'Gagal, instruktur tidak ditemukan.',
            ];
            return $this->respond($response, 200);
        }
        $id_instruktur = $instruktur['id_instruktur'];
        $id_pengganti = $instrukturpengganti['id_instruktur'];
        $Modelkelas = new Modelkelas();
        $kelas = $Modelkelas->where('nama_kelas', $nama_kelas)->first();
        if ($kelas === null) {
            $response = [
                'status' => 200,
                'error' => "true",
                'message' => 'Gagal, instruktur tidak ditemukan.',
            ];
            return $this->respond($response, 200);
        }
        $id_kelas = $kelas['id_kelas'];
        $modeljadwalUmum = new Modeljadwalumum();
        $jadwal = $modeljadwalUmum->where('id_kelas',$id_kelas)->where('jam',$jam)->where('id_instruktur',$id_instruktur)->first();
        if ($jadwal === null) {
            $response = [
                'status' => 200,
                'error' => "true",
                'message' => $jam,
            ];
            return $this->respond($response, 200);
        }
        $id_jadwal = $jadwal['id'];
        $modeljadwalHarian = new Modeljadwalharian();
        $jadwalharian = $modeljadwalHarian->where('tanggal_kelas',$tanggal)->where('jadwal',$id_jadwal)->first();
        if ($jadwalharian === null) {
            $response = [
                'status' => 200,
                'error' => "true",
                'message' => $id_jadwal,
            ];
            return $this->respond($response, 200);
        }
        $id_jadwalharian = $jadwalharian['id'];
        $modeljadwalHarian->update($id_jadwalharian, ['id_instruktur' => $id_pengganti]);
        $Modelizininstruktur->insert([
            'id_jadwal' => $id_jadwalharian,
            'status' => 'pending',
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
        $data = $model->find($id);
        if (empty($data)) {
            $response = [
                'status' => 404,
                'error' => "true",
                'message' => "Data not found",
            ];
            return $this->respond($response, 404);
        }
        $data['status'] = 'Confirmed';
        // Check if the `id_jadwal` key is present in the `$data` array
        if (!isset($data['id_jadwal'])) {
            $response = [
                'status' => 400,
                'error' => "true",
                'message' => "id_jadwal is required",
            ];
            return $this->respond($response, 400);
        }

        // Update the `jadwalharian` table
        $modelJadwalHarian = new Modeljadwalharian();
        $modelJadwalHarian->where('id', $data['id_jadwal'])
            ->set(['status' => 'Libur'])
            ->update();

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
