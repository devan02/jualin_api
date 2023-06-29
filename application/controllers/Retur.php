<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Retur extends REST_Controller
{
	public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");
        $this->load->model('retur_m', 'model');
        $this->load->model('supplier_m', 'supplier_m');
        $this->load->model('barang_m', 'barang_m');
        // Load Authorization Library or Load in autoload config file
        // $this->load->library('authorization_token');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD, OPTIONS");
    }

    function add_leading_zero($value, $threshold = 3) {
        return sprintf('%0' . $threshold . 's', $value);
    }

    function no_retur_get()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            # code... RETUR-202204001
            $nomor = $this->nomor_m->get_nomor_retur();
            $kode = 'RETUR-'.date('Y').date('m').$this->add_leading_zero($nomor);
            $arrayName = array('no_retur' => $kode);

            $this->response(
                array('success' => 200, 'result' => $arrayName),
                REST_Controller::HTTP_OK
            );

            return;
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

    function index_get()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $id_supplier = $this->get('id_supplier');
            $tanggal = $this->get('tanggal');
            $meta = $this->get('metadata');

            $data = $this->model->getRetur($id_supplier, $tanggal);

            if(!empty($data)){
                if($meta == 'true'){
                    $this->response(
                        array('success' => 200, 'total_data' => count($data), 'result' => $data),
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
                    array('message' => 'Data retur tidak ditemukan', 'error' => 404), 
                    REST_Controller::HTTP_NOT_FOUND
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

    function detail_get()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $no_retur = $this->get('no_retur');

            $data = $this->model->getReturDetail($no_retur);

            if(!empty($data)){                
                $this->response(
                    array('success' => 200, 'total_data' => count($data), 'result' => $data),
                    REST_Controller::HTTP_OK
                );
                return;
            }else{
                $this->response(
                    array('message' => 'Data retur tidak ditemukan', 'error' => 404), 
                    REST_Controller::HTTP_NOT_FOUND
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

    function index_post()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $no_retur = $this->post('nomor_retur');
            $tgl_retur = $this->post('tanggal_retur');
            $id_supplier = $this->post('id_supplier');
            $supplier = "";
            $total_harga = $this->post('total_harga');
            $total_qty = $this->post('total_item');

            if($no_retur == "" || $no_retur == null){
                $this->response(
                    array('message' => 'No Retur tidak boleh kosong', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            if($id_supplier == "" || $id_supplier == null){
                $this->response(
                    array('message' => 'Supplier tidak boleh kosong', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            $checkSupplier = $this->supplier_m->getSupplierById($id_supplier);
            if(empty($checkSupplier)){
                $this->response(
                    array('message' => 'Supplier yang anda cari tidak ada', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }else{
                $dataSupplier = $this->supplier_m->getSupplierById($id_supplier);
                $supplier = $dataSupplier['NAMA_SUPPLIER'];
            }

            if($tgl_retur == "" || $tgl_retur == null){
                $this->response(
                    array('message' => 'Tanggal retur tidak boleh kosong', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            $value = array(
                'NO_RETUR' => $no_retur,
                'TANGGAL_RETUR' => $tgl_retur,
                'ID_SUPPLIER' => $id_supplier,
                'SUPPLIER' => $supplier,
                'TOTAL_HARGA' => $total_harga,
                'TOTAL_ITEM' => $total_qty,
                'CREATE_DATE' => date('Y-m-d H:i:s')
            );

            $insert = $this->model->postRetur($value);

            if(!isset($insert)){
                $this->response(array('message' => 'Gagal menambah data', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST);
                return;
            }else{
                $this->nomor_m->check_nomor_retur(); // nomor auto
                $data = $this->model->getReturAfterInsert();

                $this->response(
                    array('success' => 201, 'message' => 'Barang berhasil diretur', 'result' => $data),
                    REST_Controller::HTTP_CREATED
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

    function detail_retur_post()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $no_retur = $this->post('nomor_retur');
            $id_barang = $this->post('id_barang');
            $plu = $this->post('plu');
            $qty = $this->post('qty');
            $harga_beli = str_replace(',', '', $this->post('harga_beli'));
            $harga_jual = str_replace(',', '', $this->post('harga_jual'));
            $subtotal = str_replace(',', '', $this->post('subtotal'));
            $createDate = date('Y-m-d H:i:s');

            if($no_retur == "" || $no_retur == null){
                $this->response(
                    array('message' => 'No LPB tidak boleh kosong', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            if($plu == "" || $plu == null){
                $this->response(
                    array('message' => 'PLU tidak boleh kosong', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            $checkBarang = $this->barang_m->getBarangByPlu($plu);
            if(empty($checkBarang)){
                $this->response(
                    array('message' => 'Barang yang anda cari tidak ada', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            if($qty == "" || $qty == null || $qty == 0){
                $this->response(
                    array('message' => 'Qty tidak boleh kosong atau 0', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            $value = array(
                'NO_RETUR' => $no_retur,
                'ID_BARANG' => $id_barang,
                'PLU' => $plu,
                'HARGA_BELI' => $harga_beli,
                'HARGA_JUAL' => $harga_jual,
                'QTY' => $qty,
                'SUBTOTAL' => $subtotal,
                'CREATE_DATE' => $createDate
            );

            $insert = $this->model->postDetailRetur($value);

            if(!isset($insert)){
                $this->response(array('message' => 'Gagal menambah data', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST);
                return;
            }else{
                //kondisi stok barang
                $cekBarang = $this->barang_m->getDetailBarang($id_barang);
                if($cekBarang['STOK_SAAT_INI'] == 0){ //jika stok saat ini 0 merupakan barang baru
                    $this->response(array('message' => 'Barang tidak bisa diretur karena stok habis', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST);
                    return;
                }

                if($cekBarang['STOK_SAAT_INI'] > 0){
                    $stokLama = $cekBarang['STOK_SAAT_INI'];
                    $stokNow = $cekBarang['STOK_SAAT_INI'] - $qty;
                    $sql = "UPDATE adm_barang SET STOK_LAMA = $stokLama, STOK_SAAT_INI = $stokNow WHERE ID = $id_barang";
                    $this->db->query($sql);
                }

                $data = $this->model->getReturDetail($no_retur);
                $this->response(
                    array('success' => 201, 'message' => 'Barang berhasil diretur', 'result' => $data),
                    REST_Controller::HTTP_CREATED
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

    function hapus_post()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $id = $this->post('id');
            $value = array('STATUS' => 'soft_deleted');

            $del = $this->model->hapusRetur($id, $value);

            if($del){                
                $this->response(
                    array('success' => 200, 'message' => 'Data berhasil dihapus'),
                    REST_Controller::HTTP_OK
                );
                return;
            }else{
                $this->response(
                    array('message' => 'Gagal menghapus data', 'error' => 400), 
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
                ), 
                REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
    }
}
?>