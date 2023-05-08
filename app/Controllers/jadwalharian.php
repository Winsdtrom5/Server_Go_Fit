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
        
        // Get the current week range
        $today = new DateTime();
        $monday = clone $today->modify('this week')->modify('Monday');
        $sunday = clone $today->modify('this week')->modify('Sunday');
        
        // Filter the data to include only the current week range
        $data = array_filter($data, function($row) use ($monday, $sunday) {
            $rowDate = new DateTime($row['tanggal_kelas']);
            return ($rowDate >= $monday && $rowDate <= $sunday);
        });
        
        // Define a map from day name to day index (0-6)
        $dayMap = [
            'Senin' => 0,
            'Selasa' => 1,
            'Rabu' => 2,
            'Kamis' => 3,
            'Jumat' => 4,
            'Sabtu' => 5,
            'Minggu' => 6,
        ];
        
        // Sort the data by day of the week
        usort($data, function($a, $b) use ($dayMap) {
            $dayA = $dayMap[$a['hari']];
            $dayB = $dayMap[$b['hari']];
            return $dayA - $dayB;
        });
        
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
        $Modeljadwalharian = new Modeljadwalharian();
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
    
        // Get the start date of the current week
        $startDate = new DateTime();
        $startDate->modify("-{$currentDayOfWeek} days");
    
        // Loop through the data and insert records into the jadwalharian table
        foreach ($dataJadwalumum as $row) {
            $hari = $row['hari'];
            $englishDay = $dayMap[$hari];
    
            // Get the date of the next occurrence of the given day of the week starting from the current date
            $nextOccurrence = new DateTime();
            $nextOccurrence->setTimestamp(strtotime("next $englishDay", $startDate->getTimestamp()));
    
            // Format the date as required for the tanggal_kelas field
            $tanggal_kelas = $nextOccurrence->format('Y-m-d') . ' s/d ' . $nextOccurrence->modify('+6 days')->format('Y-m-d');
    
            $Modeljadwalharian->insert([
                'jadwal' => $row['id'],
                'tanggal_kelas' => $tanggal_kelas,
                'id_instruktur' => $row['id_instruktur'],
                'id_kelas' => $row['id_kelas'],
                'status' => 'scheaduled'
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
        $data = $model->find($id);
        if (empty($data)) {
            $response = [
                'status' => 404,
                'error' => "true",
                'message' => "Data not found",
            ];
            return $this->respond($response, 404);
        }
        
        $request_data = $this->request->getJSON(true);
        $status = $request_data['status'] ?? null;
        $nama = $request_data['nama'] ?? null;
        
        if (empty($nama)) {
            $response = [
                'status' => 400,
                'error' => "true",
                'message' => "Nama is required",
            ];
            return $this->respond($response, 400);
        }      
        if($status != 'Digantikan'){
            $data['id_instruktur'] = null;
        }else{
            $Modelinstruktur = new Modelinstruktur();
            $instruktur = $Modelinstruktur->where('nama', $nama)->first();
    
            if ($instruktur == null) {
                $response = [
                    'status' => 404,
                    'error' => "true",
                    'message' => "Instruktur not found",
                ];
                return $this->respond($response, 404);
            }
    
            $id_instruktur = $instruktur['id_instruktur'];
            $data['id_instruktur'] = $id_instruktur;
        }
        $data['status'] = $status;
        
        $model->update($id, $data);

        $response = [
            'status' => 200,
            'error' => null,
            'message' => $id_instruktur,
        ];

        return $this->respond($response, 200);
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
