<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelmember;
use App\Models\Modeljadwalharian;
use App\Models\Modeljadwalumum;
use App\Models\Modelkelas;
use App\Controllers\BaseController;
use App\Models\Modelbookingkelas;
use DateTime;

class bookingkelas extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $Modelbookingkelas = new Modelbookingkelas();
        $data = $Modelbookingkelas->select('bookingkelas.*, jadwalharian.tanggal_kelas,jadwalumum.jam,
            jadwalumum.jam,kelas.nama_kelas,instruktur.nama,member.nama_member')
            ->join('jadwalharian', 'bookingkelas.id_jadwal = jadwalharian.id')
            ->join('jadwalumum', 'jadwalharian.jadwal = jadwalumum.id')
            ->join('member', 'bookingkelas.id_member = member.id_member')
            ->join('kelas', 'jadwalumum.id_kelas = kelas.id_kelas')
            ->join('instruktur', 'jadwalumum.id_instruktur = instruktur.id_instruktur') // First order by hari in ascending order// Then order by hari in ascending order
            ->findAll();

        foreach ($data as &$row) {
            unset($row['id_pegawai'], $row['id_promo'], $row['id_member'], $row['id_jadwal']);
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

    public function show($nama = null)
    {
        $Modelbookingkelas = new Modelbookingkelas();
        $data = $Modelbookingkelas->select('bookingkelas.*, jadwalharian.tanggal_kelas,jadwalumum.jam,
        jadwalumum.jam,kelas.nama_kelas,instruktur.nama,member.nama_member')
            ->join('jadwalharian', 'bookingkelas.id_jadwal = jadwalharian.id')
            ->join('jadwalumum', 'jadwalharian.jadwal = jadwalumum.id')
            ->join('member', 'bookingkelas.id_member = member.id_member')
            ->join('kelas', 'jadwalumum.id_kelas = kelas.id_kelas')
            ->join('instruktur', 'jadwalumum.id_instruktur = instruktur.id_instruktur')
            ->where('member.nama_member', $nama)
            ->get()
            ->getResult();
        if ($data) {
            // $total_deposit = 0;
            foreach ($data as $row) {
                // $total_deposit += $row->jumlah_deposit;
                unset($row->id_member, $row->id_jadwal);
            }

            // $new_data = [
            //     'nama_member' => $data[0]->nama_member,
            //     'total_deposit' => $total_deposit,
            // ];

            $response = [
                'status' => 200,
                'error' => false,
                'message' => '',
                'totaldata' => 1,
                'data' => $data,
            ];
            return $this->respond($response, 200);
        } else {
            return $this->failNotFound('Maaf, data kelas ' . $nama . ' tidak ditemukan');
        }
    }


    public function create()
    {
        $Modelbookingkelas = new Modelbookingkelas();
        $nama_member = $this->request->getPost("nama_member");
        $nama_kelas = $this->request->getPost("nama_kelas");
        $tanggal = $this->request->getPost("tanggal");
        $jam = $this->request->getPost("jam");
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
        $jadwal = $modeljadwalUmum->where('id_kelas', $id_kelas)->where('jam', $jam)->first();
        $id_jadwal = $jadwal['id'];
        $modeljadwalHarian = new Modeljadwalharian();
        $jadwalharian = $modeljadwalHarian->where('tanggal_kelas', $tanggal)->where('jadwal', $id_jadwal)->first();
        $id_jadwalharian = $jadwalharian['id'];
        $newSisaPeserta = $jadwalharian['sisa_peserta'] - 1;  // Decrease sisa_peserta by 1 or update it according to your logic
        $data = $modeljadwalHarian->find($id_jadwalharian);
        $data['sisa_peserta'] = $newSisaPeserta;
        $modeljadwalHarian->update($id_jadwalharian, $data);
        $Modelbookingkelas = new Modelbookingkelas();
        $check = $Modelbookingkelas->where('id_member',$id_member)->where('id_jadwal',$id_jadwalharian);
        if($check){
            $response = [
                'status' => 200,
                'error' => "true",
                'message' => 'Sudah Booking Kelas Ini',
            ];
            return $this->respond($response, 200);
        }else{
            $Modelbookingkelas->insert([
                'id_member' => $id_member,
                'id_jadwal' => $id_jadwalharian
            ]);
            $response = [
                'status' => 201,
                'error' => "false",
                'message' => $newSisaPeserta
            ];
            return $this->respond($response, 201);
        }
    }

    public function update($id = null)
    {
        $model = new Modelbookingkelas();
        $data = $this->request->getJSON(true);
        $jumlah_deposit = $this->request->getVar("jumlah_deposit");
        $data['id_pegawai'] = $jumlah_deposit;
        $model->update($id, $data);
        $response = [
            'status' => 200,
            'error' => null,
            'message' => "Done"
        ];
        return $this->respond($response, 201);
    }
}
