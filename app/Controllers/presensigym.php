<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelmember;
use App\Models\Modeljadwalharian;
use App\Models\Modeljadwalumum;
use App\Models\Modelkelas;
use App\Controllers\BaseController;
use App\Models\Modelbookinggym;
use App\Models\Modelpresensigym;
use DateTime;

class presensigym extends BaseController
{
    use ResponseTrait;
    public function index()
    {
        $Modelpresensigym = new Modelpresensigym();
        $data = $Modelpresensigym->select('presensi_gym.*,bookinggym.*,member.nama_member')
            ->join('bookinggym', 'presensi_gym.id_booking = bookinggym.id')
            ->join('member', 'bookinggym.id_member = member.id_member')
            ->findAll();

        foreach ($data as &$row) {
            unset($row['id_booking'], $row['id_member']);
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

    public function show($nama_member = null, $tanggal = null, $jam_masuk = null, $jam_keluar = null)
    {
        if($tanggal == null && $jam_masuk ==  null && $jam_keluar == null){
            $Modelpresensigym = new Modelpresensigym();
            $data = $Modelpresensigym->select('presensi_gym.*,bookinggym.*,member.nama_member')
            ->join('bookinggym', 'presensi_gym.id_booking = bookinggym.id')
            ->join('member', 'bookinggym.id_member = member.id_member')
            ->where('member.nama_member', $nama_member)
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
                return $this->failNotFound('Maaf, data kelas ' . $nama_member . ' tidak ditemukan');
            }
        }else{
            $Modelpresensigym = new Modelpresensigym();
            $data = $Modelpresensigym->select('presensi_gym.*,bookinggym.*,member.nama_member')
                ->join('bookinggym', 'presensi_gym.id_booking = bookinggym.id')
                ->join('member', 'bookinggym.id_member = member.id_member')
                ->where('member.nama_member', $nama_member)
                ->where('bookinggym.tanggal', $tanggal)
                ->where('bookinggym.jam_masuk', $jam_masuk)
                ->where('bookinggym.jam_keluar', $jam_keluar)
                ->get()
                ->getResult();
            if ($data) {
                // $total_deposit = 0;
                // foreach ($data as $row) {
                //     $total_deposit += $row->jumlah_deposit;
                // }

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
                return $this->failNotFound('Maaf, data kelas ' . $nama_member . ' tidak ditemukan');
            }
        }
    }


    public function create()
    {
        $Modelpresensigym = new Modelpresensigym();
        $nama_member = $this->request->getPost("nama_member");
        $jam_keluar = $this->request->getPost("jam_keluar");
        $tanggal = $this->request->getPost("tanggal");
        $jam_masuk = $this->request->getPost("jam_masuk");
        $status = $this->request->getPost("status");
        $Modelmember = new Modelmember();
        $member = $Modelmember->where('nama_member', $nama_member)->first();
        if ($member == null) {
            $response = [
                'status' => 200,
                'error' => "true",
                'message' => 'Gagal, member tidak ditemukan.',
            ];
            return $this->respond($response, 200);
        }
        $id_member = $member['id_member'];
        $ModelBookingGym = new Modelbookinggym();
        $bookinggym = $ModelBookingGym->where('id_member',$id_member)
        ->where('tanggal',$tanggal)->where('jam_masuk',$jam_masuk)->where('jam_keluar',$jam_keluar)
        ->first();
        if ($bookinggym === null) {
            $response = [
                'status' => 200,
                'error' => "true",
                'message' => 'Gagal, member tidak ditemukan.',
            ];
            return $this->respond($response, 200);
        }
        $id_booking = $bookinggym['id'];
        $Modelpresensigym->insert([
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
        $model = new Modelpresensigym();
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
