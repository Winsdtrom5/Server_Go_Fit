<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelmember;
use App\Models\Modelpromopaket;
use App\Models\Modelpegawai;
use App\Models\Modelkelas;
use App\Controllers\BaseController;
use App\Models\Modeldepositkelas;
use DateTime;

class depositkelas extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $Modeldepositkelas = new Modeldepositkelas();
        $data = $Modeldepositkelas->select('depositkelas.*, pegawai.nama_pegawai 
        ,kelas.nama_kelas, member.nama_member, promo_paket.nama_promo')
            ->join('pegawai', 'depositkelas.id_pegawai = pegawai.id_pegawai')
            ->join('member', 'depositkelas.id_member = member.id_member') 
            ->join('kelas','depositkelas.id_kelas = kelas.id_kelas')
            ->join('promo_paket','depositkelas.id_promo = promo_paket.id_promo', 'left')// First order by hari in ascending order// Then order by hari in ascending order
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
        $Modeldepositkelas = new Modeldepositkelas();
        $data = $Modeldepositkelas->select('depositkelas.*, kelas.* , member.*')
            ->join('kelas', 'depositkelas.id_kelas = kelas.id_kelas')
            ->join('member', 'depositkelas.id_member = member.id_member')
            ->where('kelas.nama_kelas', $nama_kelas)
            ->where('member.nama_member',$nama)
            ->get()
            ->getResult();
        if ($data) {
            $total_deposit = 0;
            foreach ($data as $row) {
                $total_deposit += $row->jumlah_deposit;
            }        

            $new_data = [
                'nama_kelas' => $data[0]->nama_kelas,
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
            return $this->failNotFound('Maaf, data ' . $nama . ' tidak ditemukan atau password salah');
        }
    }
     

    public function create()
    {
        $Modeldepositkelas = new Modeldepositkelas();
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
            $Modeldepositkelas->insert([
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
        $Modeldepositkelas->insert([
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
        $model = new Modeldepositkelas();
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