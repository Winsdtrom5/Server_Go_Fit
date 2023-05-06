<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\Modelaktivasi;
use App\Models\Modelpegawai;
use App\Models\Modelmember;
use App\Controllers\BaseController;
use DateInterval;
use DateTime;

class aktivasi extends BaseController
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
        $Modelaktivasi = new Modelaktivasi();
        $data = $Modelaktivasi->select('aktivasi.*, pegawai.nama_pegawai , member.*')
            ->join('pegawai', 'aktivasi.id_pegawai = pegawai.id_pegawai')
            ->join('member', 'aktivasi.id_member = member.id_member') // First order by hari in ascending order// Then order by hari in ascending order
            ->findAll();

        foreach ($data as &$row) {
            $row['id_pegawai'] = $row['nama_pegawai'];
            unset($row['id_pegawai']);
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
        $Modelaktivasi = new Modelaktivasi();
        $data = $Modelaktivasi->select('aktivasi.*, pegawai.nama , member.nama_member, member.tarif, TIME_FORMAT(aktivasi.jam, "%H:%i") as jam')
            ->join('pegawai', 'aktivasi.id_pegawai = pegawai.id_pegawai')
            ->join('member', 'aktivasi.id_member = member.id_member')
            ->where('aktivasi.hari', $hari)
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
        $Modelaktivasi = new Modelaktivasi();
        $tanggal = $this->request->getPost("tanggal");
        $harga = $this->request->getPost("harga");
        $email = $this->request->getPost("nama");
        $nama_member = $this->request->getPost("nama_member");

        $Modelpegawai = new Modelpegawai();
        $pegawai = $Modelpegawai->where('email', $email)->first();

        $id_pegawai = $pegawai['id_pegawai'];
        $Modelmember = new Modelmember();
        $member = $Modelmember->where('nama_member', $nama_member)->first();
        $id_member = $member['id_member'];
        $Modelaktivasi->insert([
            'tanggal' => $tanggal,
            'harga' => $harga,
            'id_pegawai' => $id_pegawai,
            'id_member' => $id_member
        ]);
        $response = [
            'status' => 201,
            'error' => "false",
            'message' => "Register Berhasil"
        ];
        return $this->respond($response, 201);
    }


    public function update($id = null)
    {
        $model = new Modelaktivasi();
        $data = $this->request->getJSON(true);
        $nama = $this->request->getVar("nama");
        $nama_member = $this->request->getVar("nama_member");
        $Modelpegawai = new Modelpegawai();
        $pegawai = $Modelpegawai->where('nama', $nama)->first();
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
        $id_member = $member['id_member'];
        $data['tanggal'] = $this->request->getVar("tanggal");
        $data['harga'] = $this->request->getVar("harga");
        $data['id_member'] = $id_member;
        $data['id_pegawai'] = $id_pegawai;
        $model->update($id, $data);
        $response = [
            'status' => 200,
            'error' => null,
            'message' => "Done"
        ];
        return $this->respond($response, 201);
    }


    public function delete($id_aktivasi)
    {
        $Modelaktivasi = new Modelaktivasi();
        $cekData = $Modelaktivasi->find($id_aktivasi);
        if ($cekData) {
            $Modelaktivasi->delete($id_aktivasi);
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
