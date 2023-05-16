<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelmember;
use App\Models\Modeljadwalharian;
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
            ->join('jadwa', 'bookingkelas.id_pegawai = pegawai.id_pegawai')
            ->join('member', 'bookingkelas.id_member = member.id_member')
            ->join('kelas', 'bookingkelas.id_kelas = kelas.id_kelas')
            ->join('promo_paket', 'bookingkelas.id_promo = promo_paket.id_promo', 'left') // First order by hari in ascending order// Then order by hari in ascending order
            ->findAll();

        foreach ($data as &$row) {
            unset($row['id_pegawai'], $row['id_promo'], $row['id_member']);
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

    public function show($nama = null, $nama_kelas = null)
    {
        if ($nama_kelas == null) {
            $Modelbookingkelas = new Modelbookingkelas();
            $data = $Modelbookingkelas->select('bookingkelas.*, member.*')
                ->join('member', 'bookingkelas.id_member = member.id_member')
                ->where('member.nama_member', $nama)
                ->get()
                ->getResult();
            if ($data) {
                $total_deposit = 0;
                foreach ($data as $row) {
                    $total_deposit += $row->jumlah_deposit;
                }

                $new_data = [
                    'nama_member' => $data[0]->nama_member,
                    'total_deposit' => $total_deposit,
                ];

                $response = [
                    'status' => 200,
                    'error' => false,
                    'message' => '',
                    'totaldata' => 1,
                    'data' => $new_data,
                ];
                return $this->respond($response, 200);
            } else {
                return $this->failNotFound('Maaf, data kelas ' . $nama_kelas . ' tidak ditemukan');
            }
        } else {
            $Modelbookingkelas = new Modelbookingkelas();
            $data = $Modelbookingkelas->select('bookingkelas.*, kelas.* , member.*')
                ->join('kelas', 'bookingkelas.id_kelas = kelas.id_kelas')
                ->join('member', 'bookingkelas.id_member = member.id_member')
                ->where('kelas.nama_kelas', $nama_kelas)
                ->where('member.nama_member', $nama)
                ->get()
                ->getResult();
            // if ($data) {
            //     $total_deposit = 0;
            //     foreach ($data as $row) {
            //         $total_deposit += $row->jumlah_deposit;
            //     }

            //     $new_data = [
            //         'nama_kelas' => $data[0]->nama_kelas,
            //         'nama_member' => $data[0]->nama_member,
            //         'total_deposit' => $total_deposit,
            //     ];

                $response = [
                    'status' => 200,
                    'error' => false,
                    'message' => '',
                    'totaldata' => 1,
                    'data' => $data,
                ];
                return $this->respond($response, 200);
            // } else {
            //     return $this->failNotFound('Maaf, data ' . $nama . ' tidak ditemukan atau password salah');
            // }
        }
    }


    public function create()
    {
        $Modelbookingkelas = new Modelbookingkelas();
        $nama_member = $this->request->getPost("nama_member");
        $nama_kelas = $this->request->getPost("nama_kelas");
        $jumlah_deposit = $this->request->getPost("jumlah_deposit");
        $tanggal = $this->request->getPost("tanggal");
        $harga = $this->request->getPost("harga");
        $email = $this->request->getPost("email");
        $Modelpegawai = new Modelpegawai();
        $pegawai = $Modelpegawai->where('email', $email)->first();
        if ($pegawai === null) {
            $response = [
                'status' => 200,
                'error' => "false",
                'message' => 'Gagal',
            ];
            return $this->respond($response, 200);
        }
        $id_pegawai = $pegawai['id_pegawai'];
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
        $promo = $this->request->getPost("promo");
        $Modelpromo = new Modelpromopaket();
        $promo = $Modelpromo->where('id_promo', $promo)->first();
        if ($promo === null) {
            $Modelbookingkelas->insert([
                'tanggal' => $tanggal,
                'harga' => $harga,
                'id_pegawai' => $id_pegawai,
                'id_member' => $id_member,
                'id_kelas' => $id_kelas,
                'jumlah_deposit' => $jumlah_deposit,
            ]);
            $response = [
                'status' => 201,
                'error' => "false",
                'message' => "save tanpa promo Berhasil"
            ];
            return $this->respond($response, 201);
        }
        $id_promo = $promo['id_promo'];
        $jumlah_deposit = $jumlah_deposit + $promo['bonus'];
        $batas = $promo['batas_berlaku'];
        $batasDate = new DateTime();
        $batasDate->modify('+' . $batas . ' month');
        $batasString = $batasDate->format('Y-m-d');
        $Modelbookingkelas->insert([
            'tanggal' => $tanggal,
            'harga' => $harga,
            'id_pegawai' => $id_pegawai,
            'id_member' => $id_member,
            'id_kelas' => $id_kelas,
            'jumlah_deposit' => $jumlah_deposit,
            'batas_berlaku' => $batasString,
            'id_promo' => $id_promo,
        ]);
        $response = [
            'status' => 201,
            'error' => "false",
            'message' => "save memakai promo Berhasil"
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
}
