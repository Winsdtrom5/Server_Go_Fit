<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelinstruktur;
use App\Models\Modeljadwalharian;
use App\Models\Modeljadwalumum;
use App\Models\Modelkelas;
use App\Controllers\BaseController;
use App\Models\Modelpresensiinstruktur;
use DateTime;

class presensiinstruktur extends BaseController
{
    use ResponseTrait;
    public function index()
    {
        $Modelpresensiinstruktur = new Modelpresensiinstruktur();
        $data = $Modelpresensiinstruktur->select('presensi_instruktur.*, jadwalharian.tanggal_kelas,jadwalumum.jam,
            jadwalumum.jam,kelas.nama_kelas,instruktur.nama')
            ->join('jadwalharian', 'presensi_instruktur.id_jadwal = jadwalharian.id')
            ->join('jadwalumum', 'jadwalharian.jadwal = jadwalumum.id')
            ->join('instruktur', 'presensi_instruktur.id_instruktur = instruktur.id_instruktur')
            ->join('kelas', 'jadwalumum.id_kelas = kelas.id_kelas')
            ->findAll();

        foreach ($data as &$row) {
            unset($row['id_pegawai'], $row['id_promo'], $row['id_instruktur'],$row['id_jadwal']);
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

    public function show($nama_kelas = null,$tanggal = null, $jam = null)
    {
        $Modelpresensiinstruktur = new Modelpresensiinstruktur();
        $data = $Modelpresensiinstruktur->select('presensi_instruktur.*, jadwalharian.tanggal_kelas,jadwalumum.jam,
            jadwalumum.jam,kelas.nama_kelas,instruktur.nama,instruktur.nama,bookingkelas.jenis')
            ->join('bookingkelas', 'presensi_instruktur.id_booking = bookingkelas.id')
            ->join('instruktur', 'bookingkelas.id_instruktur = instruktur.id_instruktur')
            ->join('jadwalharian', 'bookingkelas.id_jadwal = jadwalharian.id')
            ->join('jadwalumum', 'jadwalharian.jadwal = jadwalumum.id')
            ->join('kelas', 'jadwalumum.id_kelas = kelas.id_kelas')
            ->join('instruktur', 'jadwalumum.id_instruktur = instruktur.id_instruktur')
            ->where('kelas.nama_kelas', $nama_kelas)
            ->where('jadwalharian.tanggal_kelas',$tanggal)
            ->where('jadwalumum.jam',$jam)
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
            return $this->failNotFound('Maaf, data kelas ' . $nama_kelas . ' tidak ditemukan');
        }
    }


    public function create()
    {
        $Modelpresensiinstruktur = new Modelpresensiinstruktur();
        $nama = $this->request->getPost("nama");
        $nama_kelas = $this->request->getPost("nama_kelas");
        $tanggal = $this->request->getPost("tanggal");
        $jam = $this->request->getPost("jam");
        $status = $this->request->getPost("status");
        $Modelinstruktur = new Modelinstruktur();
        $instruktur = $Modelinstruktur->where('nama', $nama)->first();
        if ($instruktur === null) {
            $response = [
                'status' => 200,
                'error' => "true",
                'message' => 'Gagal, instruktur tidak ditemukan.',
            ];
            return $this->respond($response, 200);
        }
        $id_instruktur = $instruktur['id_instruktur'];
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
        $jadwal = $modeljadwalUmum->where('id_kelas',$id_kelas)->where('jam',$jam)->first();
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
        $id_jadwalharian = $jadwalharian['id'];
        $Modelpresensiinstruktur->insert([
            'id_instruktur' => $id_instruktur,
            'id_jadwal' => $id_jadwalharian,
            'status' => $status,
        ]);
        $response = [
            'status' => 201,
            'error' => "false",
            'message' => "Done"
        ];
        return $this->respond($response, 201);
    }

    public function update($id = null)
    {
        $model = new Modelpresensiinstruktur();
        $data = $this->request->getJSON(true);
        $status = $this->request->getVar("status");
        $data['status'] = $status;
        $model->update($id, $data);
        $response = [
            'status' => 200,
            'error' => null,
            'message' => "Done"
        ];
        return $this->respond($response, 201);
    }
}
