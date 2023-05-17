<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelmember;
use App\Models\Modelpromoreguler;
use App\Models\Modelpegawai;
use App\Models\Modelkelas;
use App\Controllers\BaseController;
use App\Models\Modeldeposituang;
use DateTime;

class deposituang extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $Modeldeposituang = new Modeldeposituang();
        $data = $Modeldeposituang->select('deposituang.*, pegawai.nama_pegawai, member.nama_member, promo_paket.nama_promo')
            ->join('pegawai', 'deposituang.id_pegawai = pegawai.id_pegawai')
            ->join('member', 'deposituang.id_member = member.id_member') 
            ->join('promo_paket','deposituang.id_promo = promo_paket.id_promo', 'left')// First order by hari in ascending order// Then order by hari in ascending order
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

    public function show($nama = null)
    {
        $Modeldeposituang = new Modeldeposituang();
        $data = $Modeldeposituang->select('deposituang.* , member.*')
            ->join('member', 'deposituang.id_member = member.id_member')
            ->where('member.nama_member',$nama)
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
            return $this->failNotFound('Maaf, data ' . $nama . ' tidak ditemukan atau password salah');
        }
    }
     

    public function create()
    {
        $Modeldeposituang = new Modeldeposituang();
        $nama_member = $this->request->getPost("nama_member");
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
        $promo = $this->request->getPost("promo");
        $Modelpromo = new Modelpromoreguler();
        $promo = $Modelpromo->where('id_promo', $promo)->first();
        if ($promo === null) {
            $Modeldeposituang->insert([
                'tanggal' => $tanggal,
                'id_pegawai' => $id_pegawai,
                'id_member' => $id_member,
                'jumlah_deposit' => $jumlah_deposit,
                'harga' => $harga
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
        $Modeldeposituang->insert([
            'tanggal' => $tanggal,
            'id_pegawai' => $id_pegawai,
            'id_member' => $id_member,
            'jumlah_deposit' => $jumlah_deposit,
            'id_promo' => $id_promo,
            'harga' => $harga
        ]);
        $Modelmember->update($id_member, ['deposit_uang' => $jumlah_deposit]);
        $response = [
            'status' => 201,
            'error' => "false",
            'message' => "save memakai promo Berhasil"
        ];
        return $this->respond($response, 201);
    }

    public function update($id = null)
    {
        $model = new Modeldeposituang();
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