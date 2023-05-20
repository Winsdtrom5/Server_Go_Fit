<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelmember;
use App\Controllers\BaseController;
use App\Models\Modelbookinggym;
use DateTime;

class bookinggym extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $Modelbookinggym = new Modelbookinggym();
        $data = $Modelbookinggym->select('bookinggym.*,member.nama_member')
            ->join('member', 'bookinggym.id_member = member.id_member') // First order by hari in ascending order// Then order by hari in ascending order
            ->findAll();

        foreach ($data as &$row) {
            unset($row['id_member'], $row['id_jadwal']);
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
        $Modelbookinggym = new Modelbookinggym();
        $data = $Modelbookinggym->select('bookinggym.*,member.nama_member')
            ->join('member', 'bookinggym.id_member = member.id_member')
            ->where('member.nama_member', $nama)
            ->get()
            ->getResult();
        if ($data) {
            foreach ($data as $row) {
                unset($row->id_member, $row->id_jadwal);
            }
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
        $Modelbookinggym = new Modelbookinggym();
        $nama_member = $this->request->getPost("nama_member");
        $jamkeluar = $this->request->getPost("jam_keluar");
        $jammasuk = $this->request->getPost("jam_masuk");
        $tanggal = $this->request->getPost("tanggal");
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
        $Modelbookinggym->insert([
            'id_member' => $id_member,
            'tanggal' => $tanggal,
            'jam_masuk' => $jammasuk,
            'jam_keluar' => $jamkeluar
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
        $model = new Modelbookinggym();
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
