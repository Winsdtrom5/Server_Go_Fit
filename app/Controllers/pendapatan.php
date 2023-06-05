<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelmember;
use App\Models\Modeljadwalharian;
use App\Models\Modeljadwalumum;
use App\Models\Modelbookingkelas;
use App\Models\Modelkelas;
use App\Controllers\BaseController;
use App\Models\Modelbookinggym;
use App\Models\Modelpresensigym;
use App\Models\Modelpresensikelas;
use DateTime;

class aktivitasgym extends BaseController
{
    use ResponseTrait;

    public function index()
    {
    }

    public function show($tahun = null)
    {
        $Modelbookinggym = new Modelbookinggym();
        $Modelpresensigym = new Modelpresensigym();
        
        $data = $Modelbookinggym
            ->select('bookinggym.*, member.nama_member')
            ->join('member', 'bookinggym.id_member = member.id_member')
            ->where('YEAR(bookinggym.tanggal)', $tahun);

        $data = $data->get()->getResult();

        if ($data) {
            $filteredKelasData = [];
            foreach ($data as $jadwal) {
                $namamember = $jadwal->nama_member;
                $tanggal = $jadwal->tanggal;
                $jadwalMonth = date('m', strtotime($tanggal));

                if ($numericMonth === null || $jadwalMonth === $numericMonth) {
                    $jumlahPeserta = $Modelpresensigym
                        ->select('COUNT(DISTINCT(presensi_gym.id_booking)) AS jumlah_peserta')
                        ->join('bookinggym', 'presensi_gym.id_booking = bookinggym.id')
                        ->join('member', 'bookinggym.id_member = member.id_member')
                        ->where('member.nama_member', $namamember)
                        ->where('bookinggym.tanggal', $tanggal)
                        ->countAllResults();
                    $filteredKelasData[$namamember] = [
                        'tanggal' => $tanggal,
                        'jumlahMember' => $jumlahPeserta,
                    ];
                }
            }

            $response = [
                'status' => 200,
                'error' => false,
                'message' => '',
                'totaldata' => count($filteredKelasData),
                'data' => array_values($filteredKelasData),
            ];
            return $this->respond($response, 200);
        } else {
            return $this->failNotFound('Maaf, data tidak ditemukan');
        }
    }

    public function create()
    {
        $Modelbookingkelas = new Modelbookingkelas();
        $nama_member = $this->request->getPost("nama_member");
        $nama_kelas = $this->request->getPost("nama_kelas");
        $tanggal = $this->request->getPost("tanggal");
        $jam = $this->request->getPost("jam");
        $jenis = $this->request->getPost("jenis");
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
        if ($jadwalharian === null) {
            $response = [
                'status' => 200,
                'error' => "true",
                'message' => 'Jadwal harian tidak ditemukan.',
            ];
            return $this->respond($response, 200);
        }

        $id_jadwalharian = $jadwalharian['id'];
        $newSisaPeserta = $jadwalharian['sisa_peserta'] - 1;
        $data = $modeljadwalHarian->find($id_jadwalharian);
        $data['sisa_peserta'] = $newSisaPeserta;
        $modeljadwalHarian->update($id_jadwalharian, $data);
        $Modelbookingkelas = new Modelbookingkelas();
        $check = $Modelbookingkelas->where('id_member', $id_member)->where('id_jadwal', $id_jadwalharian)->first();
        if ($check) {
            $response = [
                'status' => 200,
                'error' => "true",
                'message' => 'Sudah Booking Kelas Ini',
            ];
            return $this->respond($response, 200);
        } else {
            if ($jenis == "reguler") {
                $data = $Modelmember->find($id_member);
                $data2 = $modeljadwalUmum->find($id_jadwal);
                $sisa_deposit = $data['deposit_uang'] - $data2['tarif'];
                $data['deposit_uang'] = $sisa_deposit;
                $Modelmember->update($id_member, $data);
            } else {
                $data = $Modelmember->find($id_member);
                $sisa_deposit = $data['deposit_kelas'] - 1;
                $data['deposit_kelas'] = $sisa_deposit;
                $Modelmember->update($id_member, $data);
            }
            $Modelbookingkelas->insert([
                'id_member' => $id_member,
                'id_jadwal' => $id_jadwalharian,
                'jenis' => $jenis
            ]);
        }

        $response = [
            'status' => 201,
            'error' => "false",
            'message' => $newSisaPeserta
        ];
        return $this->respond($response, 201);
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

    public function delete($id)
    {
        $Modelbooking = new Modelbookingkelas();
        $cekData = $Modelbooking->find($id);
        if ($cekData) {
            if ($cekData->jenis == null) {
                $modelmember = new Modelmember();
                $modelkelas = new Modelkelas();
                $member = $modelmember->find($cekData->id_member);
                $kelas = $modelkelas->find($cekData->id_kelas);
                $newSisaDeposit = $member['deposit_uang'] + $kelas['tarif'];
                $member['deposit_uang'] = $newSisaDeposit;
                $modelmember->update($cekData->id_member, $member);
            } else {
                $modelmember = new Modelmember();
                $member = $modelmember->find($cekData->id_member);
                $newSisaDeposit = $member['deposit_kelas'] + 1;
                $member['deposit_kelas'] = $newSisaDeposit;
                $modelmember->update($cekData->id_member, $member);
            }
            $Modelbooking->delete($id);
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
