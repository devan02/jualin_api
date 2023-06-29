<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Diskon extends REST_Controller
{
	public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");
        $this->load->model('diskon_m', 'model');
        $this->load->model('barang_m', 'barang_m');
        // Load Authorization Library or Load in autoload config file
        // $this->load->library('authorization_token');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD, OPTIONS");
    }

    function index_get()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $metadata = $this->get('metadata');
            $id_barang = $this->get('id_barang');
            $tgl_akhir = $this->get('tanggal_akhir');

            // if($tgl_akhir == ""){
            //     $this->response(
            //         array(
            //             'message' => 'Tanggal akhir diskon harus ditentukan',
            //             'errorKey' => 'validationEnddate',
            //             'error' => 403
            //         ), REST_Controller::HTTP_METHOD_NOT_ALLOWED
            //     );
            //     return;
            // }

            $tgl_akhir_replace = str_replace('-', '', $tgl_akhir);
            $dak = substr($tgl_akhir_replace, 0,2);
            $mak = substr($tgl_akhir_replace, 2,2);
            $yak = substr($tgl_akhir_replace, 4);
            $tanggal_search = $yak.'-'.$mak.'-'.$dak;

            $data = $this->model->getDataBarangDiskon($id_barang, $tgl_akhir, $tanggal_search);

            if($metadata == 'true'){
                $this->response(
                    array('success' => 200, 'total_data' => count($data), 'result' => $data, 'id_barang' => $id_barang),
                    REST_Controller::HTTP_OK
                );
                return;
            }else{
                $this->response(
                    array('success' => 200, 'result' => $data),
                    REST_Controller::HTTP_OK
                );
                return;
            }         
        }else{
            $this->response(
                array(
                    'data' => $auth, 
                    'errorKey' => 'unauthorized', 
                    'error' => 401
                ), 
                REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
    }

    function barang_diskon_detail_get(){
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $id_barang = $this->get('id_barang');
            $now = date('Y-m-d');

            $dataDiskonEndDate = $this->model->getBarangDiskonByEndDate($id_barang, $now);

            if(!empty($dataDiskonEndDate)){
                $this->response(
                    array('message' => 'Barang ini sudah di diskon', 'error' => 404), REST_Controller::HTTP_NOT_FOUND
                );
                return;
            }else{
                $dataBarang = $this->barang_m->getDetailBarang($id_barang);

                $this->response(
                    array('success' => 200, 'result' => $dataBarang),
                    REST_Controller::HTTP_OK
                );
                return;
            }
        }else{
            $this->response(
                array(
                    'data' => $auth, 
                    'errorKey' => 'unauthorized', 
                    'error' => 401
                ), REST_Controller::HTTP_UNAUTHORIZED
            );
            return;
        }
    }

    function index_post()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $id_barang = $this->post('id_barang');
            $plu = $this->post('plu');
            $nama_barang = $this->post('nama_barang');
            $harga = str_replace(',', '', $this->post('harga'));
            $jenis_diskon = $this->post('jenis_diskon');
            $keterangan = $this->post('keterangan');
            $qty_trigger = 0;
            $qty_bonus = 0;
            $diskon_persen = $this->post('diskon_persen');
            $diskon_rupiah = str_replace(',', '', $this->post('diskon_rp'));
            $harga_diskon = str_replace(',', '', $this->post('harga_diskon'));
            $tgl_awal = $this->post('tanggal_awal');
            $tgl_akhir = $this->post('tanggal_akhir');
            $status = $this->post('status_diskon');
            $create_date = date('Y-m-d H:i:s');

            if($jenis_diskon != 'potongan_harga'){
                $qty_trigger = $this->post('qty_diskon');
                $qty_bonus = $this->post('qty_bonus');
            }

            //25062022
            $tgl_awal_replace = str_replace('-', '', $tgl_awal);
            $tgl_akhir_replace = str_replace('-', '', $tgl_akhir);

            $daw = substr($tgl_awal_replace, 0,2);
            $maw = substr($tgl_awal_replace, 2,2);
            $yaw = substr($tgl_awal_replace, 4);
            $tanggal_awal = $yaw.'-'.$maw.'-'.$daw;

            $dak = substr($tgl_akhir_replace, 0,2);
            $mak = substr($tgl_akhir_replace, 2,2);
            $yak = substr($tgl_akhir_replace, 4);
            $tanggal_akhir = $yak.'-'.$mak.'-'.$dak;

            if($plu == ""){
                $this->response(
                    array('message' => 'Barang harus dipilih', 'error' => 404), REST_Controller::HTTP_NOT_FOUND
                );
                return;
            }

            $value = array(
                'ID_BARANG' => $id_barang,
                'PLU' => $plu,
                'NAMA_BARANG' => $nama_barang,
                'HARGA' => $harga,
                'JENIS_DISKON' => $jenis_diskon,
                'KETERANGAN' => $keterangan,
                'QTY_TRIGGER_DISKON' => $qty_trigger,
                'QTY_BONUS' => $qty_bonus,
                'DISKON_PERSEN' => $diskon_persen,
                'DISKON_RUPIAH' => $diskon_rupiah,
                'HARGA_DISKON' => $harga_diskon,
                'TANGGAL_AWAL' => $tanggal_awal,
                'TANGGAL_AKHIR' => $tanggal_akhir,
                'STATUS' => $status,
                'CREATE_DATE' => $create_date
            );
            $ins = $this->model->insertDiskon($value);

            if($ins){
                $this->response(
                    array('message' => 'Data barang berhasil disimpan', 'success' => 200, 'result' => $value),
                    REST_Controller::HTTP_OK
                );
                return;
            }else{
                $this->response(
                    array('message' => 'Barang gagal disimpan', 'error' => 404), REST_Controller::HTTP_NOT_FOUND
                );
                return;
            }
        }else{
            $this->response(
                array(
                    'data' => $auth, 
                    'errorKey' => 'unauthorized', 
                    'error' => 401
                ), REST_Controller::HTTP_UNAUTHORIZED
            );
            return;
        }
    }

    function change_status_post(){
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $id = $this->post('id');
            
            if(!empty($id)){
                $status = $this->post('status');

                if(empty($this->model->getDiskonById($id))){
                    $this->response(
                        array('message' => 'Data belum tersedia saat ini', 'error' => 400), 
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }

                if ($status == "") {
                    $this->response(
                        array('message' => 'Status kategori tidak boleh kosong', 'error' => 400), 
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }

                $arrStatus = array('actived','deactived');
                if(!in_array($status, $arrStatus)){
                    $this->response(
                        array('message' => 'Gagal mengubah status', 'error' => 400), 
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }

                $value = array(                 
                    'STATUS' => $status,
                    'MODIFY_DATE' => date('Y-m-d H:i:s') 
                );

                $this->model->updateDiskon($id,$value);

                $this->response(
                    array('success' => 200, 'message' => 'Status diskon berhasil diubah', 'result' => $this->model->getDiskonById($id)),
                    REST_Controller::HTTP_OK
                );
                return;
            }else{
                $this->response(
                    array('message' => 'Terjadi kesalahan data', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }
        }else{
           $this->response(
                array(
                    'data' => $auth, 
                    'errorKey' => 'unauthorized', 
                    'error' => 401
                ), REST_Controller::HTTP_UNAUTHORIZED
            );
            return; 
        }
    }
}
?>