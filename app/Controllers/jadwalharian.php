<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modeljadwalharian;
use App\Models\Modeljadwalumum;
use App\Models\Modelinstruktur;
use App\Models\Modelkelas;
use App\Controllers\BaseController;
use DateInterval;
use DateTime;

class jadwalharian extends BaseController
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
        $Modeljadwalharian = new Modeljadwalharian();
        $data = $Modeljadwalharian->select('jadwalharian.*, jadwalumum.hari, jadwalumum.jam, instruktur1.nama, instruktur2.nama as instruktur_pengganti, kelas.nama_kelas, kelas.tarif')
        ->join('jadwalumum', 'jadwalharian.jadwal = jadwalumum.id')
        ->join('instruktur as instruktur1', 'jadwalumum.id_instruktur = instruktur1.id_instruktur')
        ->join('kelas', 'jadwalumum.id_kelas = kelas.id_kelas')
        ->join('instruktur as instruktur2', 'jadwalharian.id_instruktur = instruktur2.id_instruktur', 'left')
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

    public function show($kelas = null)
    {
        $Modeljadwalharian = new Modeljadwalharian();
        $data = $Modeljadwalharian->select('jadwalharian.*, jadwalumum.hari,jadwalumum.jam
        ,instruktur.nama , kelas.nama_kelas, kelas.tarif')
            ->join('jadwalumum', 'jadwalharian.jadwal = jadwalumum.id')
            ->join('instruktur', 'jadwalumum.id_instruktur = instruktur.id_instruktur')
            ->join('kelas', 'jadwalumum.id_kelas = kelas.id_kelas')
            ->where('kelas.nama_kelas', $kelas)
            ->get()
            ->getResult();
    
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
            return $this->failNotFound('Maaf, data ' . $kelas . ' tidak ditemukan atau password salah');
        }
    }
     

    public function create()
    {
        $Modeljadwalharian = new Modeljadwalharian();
        $nama = $this->request->getPost("nama");
        $Modeljadwalumum = new Modeljadwalumum();
        $dataJadwalumum = $Modeljadwalumum->select('jadwalumum.*, instruktur.nama , kelas.nama_kelas, kelas.tarif')
            ->join('instruktur', 'jadwalumum.id_instruktur = instruktur.id_instruktur')
            ->join('kelas', 'jadwalumum.id_kelas = kelas.id_kelas') // First order by hari in ascending order
            ->orderBy('hari', 'ASC')
            ->orderBy('jam', 'ASC') // Then order by jam in ascending order
            ->findAll();
    
        // Map the day name to English day name
        $dayMap = [
            'Senin' => 'Monday',
            'Selasa' => 'Tuesday',
            'Rabu' => 'Wednesday',
            'Kamis' => 'Thursday',
            'Jumat' => 'Friday',
            'Sabtu' => 'Saturday',
            'Minggu' => 'Sunday',
        ];
    
        // Get the current date and day of the week as an integer (0 = Sunday, 1 = Monday, etc.)
        $currentDate = new DateTime();
        $currentDayOfWeek = $currentDate->format('w');
    
        // Get the date of the next occurrence of Sunday (i.e., the start of the next week) starting from the current date
        $nextSunday = new DateTime();
        $nextSunday->modify('next Sunday');
        $nextSunday->modify("+{$currentDayOfWeek} days");
    
        // Loop through the data and insert records into the jadwalharian table
        foreach ($dataJadwalumum as $row) {
            $hari = $row['hari'];
            $englishDay = $dayMap[$hari];
    
            // Get the date of the next occurrence of the given day of the week starting from the current date
            $nextOccurrence = new DateTime();
            $nextOccurrence->setTimestamp(strtotime("next $englishDay", $nextSunday->getTimestamp()));
    
            // Format the date as required for the tanggal_kelas field
            $tanggal_kelas = $nextOccurrence->format('Y-m-d') . ' s/d ' . $nextOccurrence->modify('+6 days')->format('Y-m-d');
    
            $Modeljadwalharian->insert([
                'jadwal' => $row['id'],
                'tanggal_kelas' => $tanggal_kelas,
                'id_instruktur' => $row['id_instruktur'],
                'id_kelas' => $row['id_kelas'],
                'status' => 'aktif'
            ]);
        }
    
        $response = [
            'status' => 201,
            'error' => "false",
            'message' => "Register Berhasil"
        ];
        return $this->respond($response, 201);
    }
    
    public function update($id = null)
    {
        $model = new Modeljadwalharian();
        $data = $this->request->getJSON(true);
        $nama = $this->request->getVar("nama");
        $nama_kelas = $this->request->getVar("nama_kelas");
        
        $model->update($id, $data);
        $response = [
            'status' => 200,
            'error' => null,
            'message' => "Done"
        ];
        return $this->respond($response, 201);
    }


    public function delete($id_jadwalharian)
    {
        $Modeljadwalharian = new Modeljadwalharian();
        $cekData = $Modeljadwalharian->find($id_jadwalharian);
        if ($cekData) {
            $Modeljadwalharian->delete($id_jadwalharian);
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
