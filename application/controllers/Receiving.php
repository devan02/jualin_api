<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Receiving extends REST_Controller
{
	public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");
        $this->load->model('receiving_m', 'model');
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

    function no_lpb_get()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            # code... LPB-202204001
            $nomor = $this->nomor_m->get_nomor_lpb();
            $kode = 'LPB-'.date('Y').date('m').$this->add_leading_zero($nomor);
            $arrayName = array('no_lpb' => $kode);

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

    function dateDiff ($d1, $d2) {
        // Return the number of days between the two dates:    
        return abs(strtotime($d2) - strtotime($d1))/86400;
    }

    function index_get()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $keyword = $this->get('keyword');
            $penanda = $this->get('penanda');
            $tglAwal = $this->get('tanggal_awal');
            $tglAkhir = $this->get('tanggal_akhir');
            $metadata = $this->get('metadata');

            if($penanda != ""){
                $whiteList = array('tanggal_datang','tanggal_diterima'); // validasi penanda tanggal
                if(!in_array($penanda,$whiteList)){
                    $this->response(
                        array('message' => 'Isikan penanda dengan kata tanggal_datang / tanggal_diterima', 'error' => 400), 
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }

                if($tglAwal == "" && $tglAkhir == ""){
                    $this->response(
                        array('message' => 'Tanggal awal dan akhir harus diisi', 'error' => 400), 
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }

                if($tglAwal == ""){
                    $this->response(
                        array('message' => 'Tanggal awal tidak boleh kosong', 'error' => 400), 
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }

                if($tglAkhir == ""){
                    $this->response(
                        array('message' => 'Tanggal akhir tidak boleh kosong', 'error' => 400), 
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }

                $date1 = new DateTime($tglAwal);
                $date2 = new DateTime($tglAkhir);

                if($date2 < $date1){
                    $this->response(
                        array('message' => 'Tanggal akhir tidak boleh kurang dari tanggal awal.', 'error' => 400), 
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }
            }

            $data = $this->model->getReceive($keyword, $penanda, $tglAwal, $tglAkhir);

            if($metadata == 'true'){
                $this->response(
                    array('result' => $data, 'success' => 200, 'total_data' => count($data)),
                    REST_Controller::HTTP_OK
                );
                return;
            }else{
                $this->response(
                    array('result' => $data, 'success' => 200),
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

    function detail_receive_get()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $idReceive = $this->get('ID_RECEIVING');

            if($idReceive == "" || $idReceive == null){
                $this->response(
                    array('message' => 'ID Receiving tidak boleh kosong', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            $data = $this->model->getDetailReceiveAfterInsert($idReceive);

            $this->response(
                array('result' => $data, 'success' => 200),
                REST_Controller::HTTP_OK
            );
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
            $no_lpb = $this->post('no_lpb');
            $tgl_tiba = $this->post('tanggal_tiba');
            $id_supplier = $this->post('id_supplier');
            $supplier = "";
            $no_faktur = $this->post('no_faktur');
            $include_ppn = $this->post('include_ppn');
            $ppn = $this->post('ppn');            
            $total_ppn = $this->post('total_ppn');
            $netto = $this->post('netto');
            $total_qty = $this->post('total_item');
            $total_harga = $this->post('total_harga');
            $createDate = date('Y-m-d H:i:s');

            if($no_lpb == "" || $no_lpb == null){
                $this->response(
                    array('message' => 'No LPB tidak boleh kosong', 'error' => 400), 
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

            if($tgl_tiba == "" || $tgl_tiba == null){
                $this->response(
                    array('message' => 'Tanggal tiba tidak boleh kosong', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            if($no_faktur == "" || $no_faktur == null){
                $this->response(
                    array('message' => 'No faktur tiba tidak boleh kosong', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            $value = array(
                'NO_LPB' => $no_lpb,
                'TANGGAL_TIBA' => $tgl_tiba,
                'ID_SUPPLIER' => $id_supplier,
                'SUPPLIER' => $supplier,
                'NO_FAKTUR' => $no_faktur,
                'INCLUDE_PPN' => $include_ppn ? 'true' : 'false',
                'PPN' => $ppn,                
                'TOTAL_PPN' => $include_ppn ? $total_ppn : 0,
                'TOTAL_HARGA' => $total_harga,
                'NETTO' => $netto,
                'TOTAL_ITEM' => $total_qty,
                'CREATE_DATE' => $createDate
            );

            $insert = $this->model->postReceiving($value);
            if(!isset($insert)){
                $this->response(array('message' => 'Gagal menambah data', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST);
                return;
            }else{
                $this->nomor_m->check_nomor_lpb(); // nomor auto
                $data = $this->model->getReceiveAfterInsert();

                $value_sup = array(
                    'PPN' => $ppn,
                    'MODIFY_DATE' => date('Y-m-d H:i:s')
                );
                $this->model->updatePpnSupplier($id_supplier, $value_sup);

                $this->response(
                    array('success' => 201, 'message' => 'Barang berhasil direceive', 'result' => $data),
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

    function detail_receive_post()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $no_lpb = $this->post('no_lpb');
            $id_barang = $this->post('id_barang');
            $plu = $this->post('plu');
            $qty = $this->post('qty');
            $harga_beli = str_replace(',', '', $this->post('harga_beli'));
            $ppn = str_replace(',', '', $this->post('ppn'));
            $ppn_per_item = $this->post('ppn_per_item');
            $ppn_rp = str_replace(',', '', $this->post('ppn_rp'));
            $harga_jual = str_replace(',', '', $this->post('harga_jual'));
            $subtotal = str_replace(',', '', $this->post('subtotal'));
            $createDate = date('Y-m-d H:i:s');

            if($no_lpb == "" || $no_lpb == null){
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

            if($harga_beli == "" || $harga_beli == null){
                $this->response(
                    array('message' => 'Harga beli tidak boleh kosong', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            $value = array(
                'NO_LPB' => $no_lpb,
                'ID_BARANG' => $id_barang,
                'PLU' => $plu,
                'HARGA_BELI' => $harga_beli,
                'PPN' => $ppn,
                'PPN_PER_ITEM' => $ppn_per_item,
                'PPN_RP' => $ppn_rp,
                'HARGA_JUAL' => $harga_jual,
                'QTY' => $qty,
                'SUBTOTAL' => $subtotal,
                'CREATE_DATE' => $createDate
            );

            $insert = $this->model->postDetailReceiving($value);
            if(!isset($insert)){
                $this->response(array('message' => 'Gagal menambah data', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST);
                return;
            }else{
                //kondisi stok barang
                $cekBarang = $this->barang_m->getDetailBarang($id_barang);
                if($cekBarang['STOK_SAAT_INI'] == 0){ //jika stok saat ini 0 merupakan barang baru
                    $valueStokNow = array(
                        'STOK_SAAT_INI' => $qty
                    );

                    $this->db->where('ID', $id_barang);
                    $this->db->update('adm_barang', $valueStokNow);
                }

                if($cekBarang['STOK_SAAT_INI'] > 0){
                    $stokLama = $cekBarang['STOK_SAAT_INI'];
                    $stokNow = $cekBarang['STOK_SAAT_INI'] + $qty;
                    $sql = "UPDATE adm_barang SET STOK_LAMA = $stokLama, STOK_SAAT_INI = $stokNow WHERE ID = $id_barang";
                    $this->db->query($sql);
                }

                //kondisi harga beli
                if($cekBarang['HARGA_BELI'] == 0){ //jika stok saat ini 0 merupakan barang baru
                    $value = array(
                        'HARGA_BELI' => $harga_beli
                    );

                    $this->db->where('ID', $id_barang);
                    $this->db->update('adm_barang', $value);
                }

                if($cekBarang['HARGA_BELI'] > 0){
                    $hargaBeliLama = $cekBarang['HARGA_BELI'];
                    $sql = "UPDATE adm_barang SET HARGA_BELI_LAMA = $hargaBeliLama, HARGA_BELI = $harga_beli WHERE ID = $id_barang";
                    $this->db->query($sql);
                }

                //kondisi harga jual
                if($cekBarang['HARGA_JUAL'] == 0){ //jika stok saat ini 0 merupakan barang baru
                    $value = array(
                        'HARGA_JUAL' => $harga_jual
                    );

                    $this->db->where('ID', $id_barang);
                    $this->db->update('adm_barang', $value);
                }

                if($cekBarang['HARGA_JUAL'] > 0){
                    $hargaJualLama = $cekBarang['HARGA_JUAL'];
                    $sql = "UPDATE adm_barang SET HARGA_JUAL_LAMA = $hargaJualLama, HARGA_JUAL = $harga_jual WHERE ID = $id_barang";
                    $this->db->query($sql);
                }

                $data = $this->model->getDetailReceiveAfterInsert($no_lpb);
                $this->response(
                    array('success' => 201, 'message' => 'Barang berhasil direceive', 'result' => $data),
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

    function index_delete($id)
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            if($id != ""){
                $this->db->where('ID', $id);
                $del = $this->db->delete('tb_receiving');

                if($del === FALSE){
                    $this->response(
                        array('message' => 'Gagal menghapus data', 'error' => 400), 
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }else{
                    $this->db->where('ID_RECEIVING', $id);
                    $this->db->delete('tb_detail_receiving');

                    $this->response(
                        array('message' => 'Data berhasil dihapus', 'success' => 200), 
                        REST_Controller::HTTP_OK
                    );
                    return;
                }
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
                ), 
                REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
    }

}
?>