<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modeljadwalumum;
use App\Models\Modelinstruktur;
use App\Models\Modelkelas;
use App\Controllers\BaseController;
use DateInterval;
use DateTime;

class jadwalumum extends BaseController
{
    use ResponseTrait;
    private $encrypt;
    private $bcrypt;
    public function __construct()
    {
        $this->encrypt = \Config\Services::encrypter();
        $this->bcrypt = \Config\Services::bcrypt();
    }
    public function index()
    {
        $Modeljadwalumum = new Modeljadwalumum();
        $data = $Modeljadwalumum->select('jadwalumum.*, instruktur.nama , kelas.nama_kelas, kelas.tarif')
            ->join('instruktur', 'jadwalumum.id_instruktur = instruktur.id_instruktur')
            ->join('kelas', 'jadwalumum.id_kelas = kelas.id_kelas') // First order by hari in ascending order// Then order by hari in ascending order
            ->findAll();

        foreach ($data as &$row) {
            $row['id_instruktur'] = $row['nama'];
            $row['id_kelas'] = $row['nama_kelas'];
            $row['hari'] = $row['hari'];
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

    public function show($nama = null, $hari = null, $jam = null)
    {
        $Modeljadwalumum = new Modeljadwalumum();
        $data = $Modeljadwalumum->select('jadwalumum.*, instruktur.nama , kelas.nama_kelas, kelas.tarif, TIME_FORMAT(jadwalumum.jam, "%H:%i") as jam')
            ->join('instruktur', 'jadwalumum.id_instruktur = instruktur.id_instruktur')
            ->join('kelas', 'jadwalumum.id_kelas = kelas.id_kelas')
            ->where('jadwalumum.hari', $hari)
            ->get()
            ->getResult();
    
        $match = false;
        $input_time = DateTime::createFromFormat('H:i', $jam);
    
        foreach ($data as $row) {
            if ($row->nama == $nama) {
                $existing_time = DateTime::createFromFormat('H:i', $row->jam);
                $end_time = clone $existing_time;
                $end_time->add(new DateInterval('PT2H'));
    
                if ($input_time >= $existing_time && $input_time <= $end_time) {
                    $match = true;
                    break;
                }
            }
        }
    
        if ($match) {
            $response = [
                'status' => 200,
                'error' => false,
                'message' => '',
                'totaldata' => 1,
                'data' => $row,
            ];
            return $this->respond($response, 200);
        } else {
            return $this->failNotFound('Maaf, data ' . $nama . ' tidak ditemukan atau password salah');
        }
    }
     

    public function create()
    {
        $Modeljadwalumum = new Modeljadwalumum();
        $hari = $this->request->getPost("hari");
        $jam = $this->request->getPost("jam");
        $nama = $this->request->getPost("nama");
        $nama_kelas = $this->request->getPost("nama_kelas");

        $Modelinstruktur = new Modelinstruktur();
        $instruktur = $Modelinstruktur->where('nama', $nama)->first();

        $id_instruktur = $instruktur['id_instruktur'];
        $Modelkelas = new Modelkelas();
        $kelas = $Modelkelas->where('nama_kelas', $nama_kelas)->first();
        $id_kelas = $kelas['id_kelas'];
        $Modeljadwalumum->insert([
            'hari' => $hari,
            'jam' => $jam,
            'id_instruktur' => $id_instruktur,
            'id_kelas' => $id_kelas
        ]);
        $response = [
            'status' => 201,
            'error' => "false",
            'message' => "Register Berhasil"
        ];
        return $this->respond($response, 201);
    }


    public function update($id = null)
    {
        $model = new Modeljadwalumum();
        $data = $this->request->getJSON(true);
        $nama = $this->request->getVar("nama");
        $nama_kelas = $this->request->getVar("nama_kelas");
        $Modelinstruktur = new Modelinstruktur();
        $instruktur = $Modelinstruktur->where('nama', $nama)->first();
        if ($instruktur === null) {
            $response = [
                'status' => 200,
                'error' => "false",
                'message' => 'Gagal',
            ];
            return $this->respond($response, 200);
        }
        $id_instruktur = $instruktur['id_instruktur'];
        $Modelkelas = new Modelkelas();
        $kelas = $Modelkelas->where('nama_kelas', $nama_kelas)->first();
        $id_kelas = $kelas['id_kelas'];
        $data['hari'] = $this->request->getVar("hari");
        $data['jam'] = $this->request->getVar("jam");
        $data['id_kelas'] = $id_kelas;
        $data['id_instruktur'] = $id_instruktur;
        $model->update($id, $data);
        $response = [
            'status' => 200,
            'error' => null,
            'message' => "Done"
        ];
        return $this->respond($response, 201);
    }


    public function delete($id_jadwalumum)
    {
        $Modeljadwalumum = new Modeljadwalumum();
        $cekData = $Modeljadwalumum->find($id_jadwalumum);
        if ($cekData) {
            $Modeljadwalumum->delete($id_jadwalumum);
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
