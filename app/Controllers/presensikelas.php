<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelmember;
use App\Models\Modeljadwalharian;
use App\Models\Modeljadwalumum;
use App\Models\Modelkelas;
use App\Controllers\BaseController;
use App\Models\Modelbookingkelas;
use App\Models\Modelpresensikelas;
use DateTime;

class presensikelas extends BaseController
{
    use ResponseTrait;
    public function index()
    {
        $Modelpresensikelas = new Modelpresensikelas();
        $data = $Modelpresensikelas->select('presensi_kelas.*, jadwalharian.tanggal_kelas,jadwalumum.jam,
            jadwalumum.jam,kelas.nama_kelas,instruktur.nama,member.nama_member,bookingkelas.jenis')
            ->join('bookingkelas','presensi_kelas.id_booking = bookingkelas.id')
            ->join('jadwalharian', 'bookingkelas.id_jadwal = jadwalharian.id')
            ->join('jadwalumum', 'jadwalharian.jadwal = jadwalumum.id')
            ->join('member', 'bookingkelas.id_member = member.id_member')
            ->join('kelas', 'jadwalumum.id_kelas = kelas.id_kelas')
            ->join('instruktur', 'jadwalumum.id_instruktur = instruktur.id_instruktur')// First order by hari in ascending order// Then order by hari in ascending order
            ->findAll();

        foreach ($data as &$row) {
            unset($row['id_pegawai'], $row['id_promo'], $row['id_member'],$row['id_jadwal']);
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
        $Modelpresensikelas = new Modelpresensikelas();
        $data = $Modelpresensikelas->select('presensi_kelas.*, jadwalharian.tanggal_kelas,jadwalumum.jam,
            jadwalumum.jam,kelas.nama_kelas,instruktur.nama,member.nama_member,bookingkelas.jenis')
            ->join('bookingkelas', 'presensi_kelas.id_booking = bookingkelas.id')
            ->join('member', 'bookingkelas.id_member = member.id_member')
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
        $Modelpresensikelas = new Modelpresensikelas();
        $nama_member = $this->request->getPost("nama_member");
        $nama_kelas = $this->request->getPost("nama_kelas");
        $tanggal = $this->request->getPost("tanggal");
        $jam = $this->request->getPost("jam");
        $status = $this->request->getPost("status");
        $Modelmember = new Modelmember();
        $member = $Modelmember->where('nama_member', $nama_member)->first();
        if ($member === null) {
            $response = [
                'status' => 200,
                'error' => "true",
                'message' => 'Gagal, member tidak ditemukan.',
            ];
            return $this->respond($response, 200);
        }
        $id_member = $member['id_member'];
        $Modelkelas = new Modelkelas();
        $kelas = $Modelkelas->where('nama_kelas', $nama_kelas)->first();
        if ($kelas === null) {
            $response = [
                'status' => 200,
                'error' => "true",
                'message' => 'Gagal, member tidak ditemukan.',
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
        $modelbookingkelas = new Modelbookingkelas();
        $bookingkelas = $modelbookingkelas->where('id_member',$id_member)->where('id_jadwal',$id_jadwalharian)->first();
        $id_booking = $bookingkelas['id'];
        $Modelpresensikelas->insert([
            'id_booking' => $id_booking,
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
        $model = new Modelpresensikelas();
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
