<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Kasir extends REST_Controller
{
	public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");
        $this->load->model('kasir_m', 'model');
        $this->load->model('barang_m', 'barang_m');
        // Load Authorization Library or Load in autoload config file
        // $this->load->library('authorization_token');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD, OPTIONS");
    }

    function add_leading_zero($value, $threshold = 4) {
        return sprintf('%0' . $threshold . 's', $value);
    }

    //GET
    function no_trx_get()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            # code... 0001
            $year = date('y');
            $month = date('m');
            $nomor = $this->nomor_m->get_nomor_trx();
            $kode = $year.$month.$this->add_leading_zero($nomor);
            $arrayName = array('no_trx' => $kode);

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

    function barang_kasir_get(){
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $keyword = $this->get('keyword');

            $data = $this->model->getBarangKasir($keyword);

            $this->response(
                array('success' => 200, 'result' => $data),
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

    function barang_kasir_by_id_get()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $id = $this->get('id');

            $data = $this->model->getBarangById($id);

            $this->response(
                array('success' => 200, 'result' => $data),
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

    function trx_detail_get()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $id = $this->get('id');
            $tanggal = date('Y-m-d');       

            if(empty($id)){
                $this->response(
                    array('message' => 'Detail transaksi tidak ada', 'error' => 404), 
                    REST_Controller::HTTP_NOT_FOUND
                );
                return;
            }

            $data = $this->model->getTransaksiById($id);
            $no_trx = $data['NO_TRX'];
            $detail = $this->model->getDetailTransaksiByNoTrxToday($no_trx, $tanggal);

            $this->response(
                array('success' => 200, 'no_trx' => $no_trx, 'result' => $data, 'detail' => $detail),
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

    function struk_get()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $no_trx = $this->get('no_trx');
            $tanggal = date('Y-m-d');

            $data = $this->model->getTransaksiByNoTrx($no_trx, $tanggal);
            $detail = $this->model->getDetailTransaksiByNoTrxTodayStruk($no_trx, $tanggal);

            if(empty($data)){
                $this->response(array('message' => 'Transaksi tidak ada', 'error' => 404), REST_Controller::HTTP_NOT_FOUND);
                return;
            }

            $this->response(
                array('success' => 200, 'message' => 'Cetak struk berhasil', 'result' => $data, 'detail' => $detail),
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
    //END

    //DISKON KASIR
    function barang_diskon_post()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $id_barang = $this->post('id_barang');
            $qty = $this->post('qty');
            $tanggal = date('Y-m-d');

            $dataDiskon = $this->model->getBarangDiskon($id_barang, $qty, $tanggal);

            if(!empty($dataDiskon)){ //barang kena diskon
                $this->response(
                    array('success' => 200, 'status' => 'diskon', 'result' => $dataDiskon),
                    REST_Controller::HTTP_OK
                );
                return;
            }else{
                $data = $this->barang_m->getDetailBarang($id_barang);
                $this->response(
                    array('success' => 200, 'status' => 'tidak_diskon', 'result' => $data),
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

    //TRANSAKSI
    function bayar_post()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $tanggal = date('Y-m-d');
            $waktu = date('H:i:s');
            $no_trx = $this->post('no_trx');      
            $total_qty = $this->post('total_qty');
            $total = $this->post('total');
            $total_diskon = $this->post('total_diskon');
            $bayar = $this->post('bayar');
            $kembali = $this->post('kembali');
            $pilihan_bayar = $this->post('pilihan_bayar');
            $jenis_kartu = ($pilihan_bayar == 'non_tunai') ? $this->post('jenis_kartu') : null;
            $nomor_kartu = ($pilihan_bayar == 'non_tunai') ? $this->post('nomor_kartu') : null;
            $penanda_status = $this->post('penanda_status');
            $status = $this->post('status');
            $result = $auth['data'];
            $id_user = $result->id;
            $user = $result->username;
            $create_date = date('Y-m-d H:i:s');

            if($penanda_status == 'loadpending'){
                $id = $this->post('id_pending'); //sing penting mari pending

                $data = $this->model->getTransaksiById($id);
                $no_trx_db = $data['NO_TRX'];

                $sql = "DELETE FROM kasir_transaksi WHERE NO_TRX = '$no_trx_db' AND TANGGAL = '$tanggal'";
                $this->db->query($sql);

                $sqldetail = "DELETE FROM kasir_detail_transaksi WHERE NO_TRX = '$no_trx_db' AND DATE(CREATE_DATE) = '$tanggal'";
                $this->db->query($sqldetail);
            }

            $checkTrxExist = $this->model->getTransaksiByNoTrx($no_trx, $tanggal);

            if(!empty($checkTrxExist)){ //jika trx sudah ada
                $nomor = $this->nomor_m->get_nomor_trx();
                $no_trx_next = $this->add_leading_zero($nomor);

                $value = array(
                    'TANGGAL' => $tanggal,
                    'WAKTU' => $waktu,
                    'NO_TRX' => $no_trx_next,
                    'TOTAL_QTY' => $total_qty,
                    'TOTAL' => str_replace(',', '', $total),
                    'TOTAL_DISKON' => str_replace(',', '', $total_diskon),
                    'BAYAR' => str_replace(',', '', $bayar),
                    'KEMBALI' => str_replace(',', '', $kembali),
                    'PILIHAN_BAYAR' => $pilihan_bayar,
                    'JENIS_KARTU' => $jenis_kartu,
                    'NOMOR_KARTU' => $nomor_kartu,
                    'STATUS' => $status,
                    'ID_USER' => $id_user,
                    'USER' => $user,
                    'CREATE_DATE' => $create_date
                );

                $this->nomor_m->check_nomor_trx();
                $this->model->postTransaksi($value);

                $this->response(
                    array(
                        'success' => 200, 
                        'message' => 'Transaksi berhasil', 
                        'no_trx' => $no_trx_next, 
                        'result' => $value
                    ), REST_Controller::HTTP_OK
                );
            }else{
                $value = array(
                    'TANGGAL' => $tanggal,
                    'WAKTU' => $waktu,
                    'NO_TRX' => $no_trx,
                    'TOTAL_QTY' => $total_qty,
                    'TOTAL' => str_replace(',', '', $total),
                    'TOTAL_DISKON' => str_replace(',', '', $total_diskon),
                    'BAYAR' => str_replace(',', '', $bayar),
                    'KEMBALI' => str_replace(',', '', $kembali),
                    'PILIHAN_BAYAR' => $pilihan_bayar,
                    'JENIS_KARTU' => $jenis_kartu,
                    'NOMOR_KARTU' => $nomor_kartu,
                    'STATUS' => $status,
                    'ID_USER' => $id_user,
                    'USER' => $user,
                    'CREATE_DATE' => $create_date
                );

                $this->nomor_m->check_nomor_trx();
                $this->model->postTransaksi($value);

                $this->response(
                    array(
                        'success' => 200, 
                        'message' => 'Transaksi berhasil', 
                        'no_trx' => $no_trx, 
                        'result' => $value
                    ), REST_Controller::HTTP_OK
                );
            }
        }else{
            $this->response(
                array(
                    'data' => $auth, 
                    'errorKey' => 'unauthorized', 
                    'error' => 401
                ), REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
    }

    function save_detail_trx_post()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){            
            $tanggal = date('Y-m-d');
            $no_trx = $this->post('no_trx');
            $id_barang = $this->post('id_barang');
            $plu = $this->post('plu');
            $nama_barang = "";
            $harga = $this->post('harga');
            $id_diskon = $this->post('id_diskon');
            $jenis_diskon = $this->post('jenis_diskon');
            $keterangan = $this->post('keterangan');
            $diskon = $this->post('diskon');
            $qty = $this->post('qty');
            $subtotal = $this->post('subtotal');
            $status = $this->post('status');
            $create_date = date('Y-m-d H:i:s');

            if($id_barang){
                $dataBarang = $this->barang_m->getDetailBarang($id_barang);
                $nama_barang = $dataBarang['NAMA_BARANG'];
            }

            $checkTrxExist = $this->model->getTransaksiByNoTrx($no_trx, $tanggal);
            if(!empty($checkTrxExist)){ //jika trx sudah ada
                // $nomor = $this->nomor_m->get_nomor_trx();
                // $no_trx_next = $this->add_leading_zero($nomor);

                $value = array(
                    'NO_TRX' => $checkTrxExist['NO_TRX'],
                    'ID_BARANG' => $id_barang,
                    'PLU' => $plu,
                    'NAMA_BARANG' => $nama_barang,
                    'HARGA' => str_replace(',', '', $harga),
                    'ID_DISKON' => $id_diskon,
                    'JENIS_DISKON' => $jenis_diskon,
                    'KETERANGAN' => $keterangan,
                    'DISKON' => str_replace(',', '', $diskon),
                    'QTY' => str_replace(',', '', $qty),
                    'SUBTOTAL' => str_replace(',', '', $subtotal),
                    'STATUS' => $status,
                    'CREATE_DATE' => $create_date
                );
                $ins = $this->model->postDetailTransaksi($value);

                if(!$ins){ //update stok barang
                    $this->response(
                        array('success' => 400, 'message' => 'Gagal simpan detail transaksi'),
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }else{
                    $sql = "UPDATE adm_barang SET STOK_SAAT_INI = STOK_SAAT_INI - $qty WHERE ID = '$id_barang'";
                    $this->db->query($sql);
                }

                $this->response(
                    array('success' => 200, 'message' => 'Transaksi berhasil', 'result' => $value),
                    REST_Controller::HTTP_OK
                );
            }else{
                $value = array(
                    'NO_TRX' => $no_trx,
                    'ID_BARANG' => $id_barang,
                    'PLU' => $plu,
                    'NAMA_BARANG' => $nama_barang,
                    'HARGA' => str_replace(',', '', $harga),
                    'ID_DISKON' => $id_diskon,
                    'JENIS_DISKON' => $jenis_diskon,
                    'KETERANGAN' => $keterangan,
                    'DISKON' => str_replace(',', '', $diskon),
                    'QTY' => str_replace(',', '', $qty),
                    'SUBTOTAL' => str_replace(',', '', $subtotal),
                    'STATUS' => $status,
                    'CREATE_DATE' => $create_date
                );
                $ins = $this->model->postDetailTransaksi($value);

                if(!$ins){ //update stok barang
                    $this->response(
                        array('success' => 400, 'message' => 'Gagal simpan detail transaksi'),
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }else{
                    $sql = "UPDATE adm_barang SET STOK_SAAT_INI = STOK_SAAT_INI - $qty WHERE ID = '$id_barang'";
                    $this->db->query($sql);
                }

                $this->response(
                    array('success' => 200, 'message' => 'Transaksi berhasil', 'result' => $value),
                    REST_Controller::HTTP_OK
                );
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

    //PENDING
    function pending_get()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $tanggal = date('Y-m-d');
            $no_trx = $this->get('no_trx');
            $metadata = $this->get('metadata');
            $page = $this->get('page');
            $limit = $this->get('limit');

            $page = $page ? $page : 0;
            $limit = $limit ? $limit : 10;

            $data = $this->model->getTrxPending($tanggal, $no_trx, $page, $limit);

            if($metadata == 'true'){
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
                array(
                    'data' => $auth, 
                    'errorKey' => 'unauthorized', 
                    'error' => 401
                ), 
                REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
    }

    function pending_cancel_post()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $id = $this->post('id');

            if($id == "" || $id == null || $id == 0 || $id == "0"){
                $this->response(
                    array('message' => 'Terjadi kesalahan data, ID kosong', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            $value = array('STATUS' => 'cancel');
            $update = $this->model->updateStatusTransaksi($id, $value);

            if($update){
                $data = $this->model->getTransaksiById($id);
                $no_trx = $data['NO_TRX'];

                $this->db->where('NO_TRX', $no_trx);
                $this->db->update('kasir_detail_transaksi', $value);

                $this->response(
                    array('success' => 200, 'message' => 'Transaksi berhasil dibatalkan', 'result' => $data),
                    REST_Controller::HTTP_OK
                );
                return;
            }else{
                $this->response(
                    array('message' => 'Gagal memperbarui status transaksi', 'error' => 400), 
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

    //RETUR
    function retur_get()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $no_trx = $this->get('no_trx');
            $tanggal = date('Y-m-d');

            if(empty($no_trx)){
                $this->response(
                    array('success' => 400, 'message' => 'Nomor transaksi harus diisi'),
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            $data = $this->model->getTransaksiByNoTrx($no_trx, $tanggal);

            if(!empty($data)){
                $dataDetail = $this->model->getDetailTransaksiByNoTrxToday($no_trx, $tanggal);
                $dataReturFinish = $this->model->getDetailTransaksiByNoTrxTodayFinish($no_trx, $tanggal);

                if(!empty($dataDetail)){                        
                    $this->response(
                        array('success' => 200, 'result' => $data, 'detail' => $dataDetail),
                        REST_Controller::HTTP_OK
                    );
                    return;
                }else{
                    if(empty($dataReturFinish)){
                        $this->response(
                            array('message' => 'Transaksi ini sudah pernah diretur', 'error' => 400), 
                            REST_Controller::HTTP_BAD_REQUEST
                        );
                        return;
                    }                    
                }
            }else{
                $this->response(
                    array('message' => 'Tidak ada transaksi dengan nomor '.$no_trx, 'error' => 404), 
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
                ), REST_Controller::HTTP_UNAUTHORIZED
            );
            return;
        }
    }

    function update_status_retur_post()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $tanggal = date('Y-m-d');
            $no_trx = $this->post('no_trx');
            $id_barang = $this->post('id_barang');
            $qty = str_replace(',', '', $this->post('qty'));

            if($no_trx == ""){
                $this->response(
                    array('message' => 'No transaksi tidak boleh kosong!', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            if($qty == ""){
                $this->response(
                    array('message' => 'Qty tidak boleh kosong!', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            if($qty == 0){
                $this->response(
                    array('message' => 'Qty tidak boleh 0!', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            $this->model->updateStatusRetur($no_trx ,$id_barang, $qty, $tanggal);

            $this->response(
                array('success' => 200, 'message' => 'Barang diretur'),
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

    function bayar_retur_post()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $tanggal = date('Y-m-d');
            $waktu = date('H:i:s');
            $no_trx = $this->post('no_trx');      
            $total_qty = $this->post('total_qty');
            $total = $this->post('total');
            $total_diskon = $this->post('total_diskon');
            $total_retur = $this->post('total_retur');
            $bayar = $this->post('bayar');
            $kembali = $this->post('kembali');
            $pilihan_bayar = $this->post('pilihan_bayar');
            $jenis_kartu = ($pilihan_bayar == 'non_tunai') ? $this->post('jenis_kartu') : null;
            $nomor_kartu = ($pilihan_bayar == 'non_tunai') ? $this->post('nomor_kartu') : null;
            $status = $this->post('status');
            $result = $auth['data'];
            $id_user = $result->id;
            $user = $result->username;
            $create_date = date('Y-m-d H:i:s');            

            $value = array(
                'TANGGAL' => $tanggal,
                'WAKTU' => $waktu,
                'NO_TRX' => $no_trx,
                'TOTAL_QTY' => $total_qty,
                'TOTAL' => str_replace(',', '', $total),
                'TOTAL_DISKON' => str_replace(',', '', $total_diskon),
                'TOTAL_RETUR' => str_replace(',', '', $total_retur),
                'BAYAR' => str_replace(',', '', $bayar),
                'KEMBALI' => str_replace(',', '', $kembali),
                'PILIHAN_BAYAR' => $pilihan_bayar,
                'JENIS_KARTU' => $jenis_kartu,
                'NOMOR_KARTU' => $nomor_kartu,
                'STATUS' => $status,
                'ID_USER' => $id_user,
                'USER' => $user,
                'CREATE_DATE' => $create_date
            );

            $this->nomor_m->check_nomor_trx();
            $this->model->postTransaksi($value);

            $this->response(
                array(
                    'success' => 200, 
                    'message' => 'Transaksi berhasil', 
                    'no_trx' => $no_trx, 
                    'result' => $value
                ), REST_Controller::HTTP_OK
            );
        }else{
            $this->response(
                array(
                    'data' => $auth, 
                    'errorKey' => 'unauthorized', 
                    'error' => 401
                ), REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
    }

    function simpan_detail_retur_post()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){            
            $tanggal = date('Y-m-d');
            $no_trx = $this->post('no_trx');
            $no_trx_retur = $this->post('no_trx_retur');
            $id_barang_retur = $this->post('id_barang_retur');
            $plu_retur = $this->post('plu_retur');
            $nama_barang_retur = "";
            $harga_retur = $this->post('harga_retur');
            $id_diskon_retur = $this->post('id_diskon_retur');
            $jenis_diskon_retur = $this->post('jenis_diskon_retur');
            $keterangan_retur = $this->post('keterangan_retur');
            $diskon_retur = $this->post('diskon_retur');
            $qty_retur = $this->post('qty_retur');
            $subtotal_retur = $this->post('subtotal_retur');
            $status = $this->post('status');
            $create_date = date('Y-m-d H:i:s');

            $dataBarangRetur = $this->barang_m->getDetailBarang($id_barang_retur);
            $nama_barang_retur = $dataBarangRetur['NAMA_BARANG'];

            $valueRetur = array(
                'NO_TRX' => $no_trx,
                'ID_BARANG' => $id_barang_retur,
                'PLU' => $plu_retur,
                'NAMA_BARANG' => $nama_barang_retur,
                'HARGA' => str_replace(',', '', $harga_retur),
                'ID_DISKON' => $id_diskon_retur,
                'JENIS_DISKON' => $jenis_diskon_retur,
                'KETERANGAN' => $keterangan_retur,
                'DISKON' => str_replace(',', '', $diskon_retur),
                'QTY' => str_replace(',', '', $qty_retur),
                'SUBTOTAL' => str_replace(',', '', $subtotal_retur),
                'STATUS' => $status,
                'STATUS_RETUR' => 'after_true',
                'CREATE_DATE' => $create_date
            );
            $this->model->postDetailTransaksi($valueRetur);

            if($id_barang_retur){
                $dataRetur = $this->model->getTransaksiReturToday($no_trx_retur, $tanggal, $id_barang_retur);

                if(!empty($dataRetur)){
                    $this->model->updateStokBarangFromRetur($id_barang_retur, $qty_retur);
                    $this->model->updateStatusReturFinish($no_trx_retur, $id_barang_retur, $tanggal);

                    $this->response(
                        array('success' => 200, 'message' => 'Barang telah diretur', 'no_trx_retur' => $no_trx_retur, 'result' => $dataRetur),
                        REST_Controller::HTTP_OK
                    );
                    return;      
                }
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

    //CLOSING
    function cek_trx_closing_get(){
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $tanggal = date('Y-m-d');

            $cekTrxToday = $this->model->cekTrxFinishToday($tanggal);

            if(empty($cekTrxToday)){
                $this->response(
                    array('message' => 'Tidak ada transaksi yang akan ditutup', 'error' => 404), 
                    REST_Controller::HTTP_NOT_FOUND
                );
                return;
            }

            $this->response(
                array('message' => 'Transaksi siap diclosing', 'success' => 200, 'result' => $cekTrxToday), 
                REST_Controller::HTTP_OK
            );
            return;
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

    function struk_closing_get(){
        $tanggal = date('Y-m-d');
        $data = $this->model->getStrukClosing($tanggal);

        if(empty($data)){
            $this->response(
                array('message' => 'Tidak ada transaksi yang ditemukan', 'error' => 404), 
                REST_Controller::HTTP_NOT_FOUND
            );
            return;
        }else{
            $this->response(
                array('success' => 200, 'result' => $data),
                REST_Controller::HTTP_OK
            );
            return;
        }
    }

    function closing_kasir_post()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $tanggal = date('Y-m-d');
            $waktu = date('H:i:s');
            $total_tunai = str_replace(',', '', $this->post('total_tunai'));
            $total_kartu = str_replace(',', '', $this->post('total_kartu'));
            $total_pengeluaran = str_replace(',', '', $this->post('total_pengeluaran'));
            $total_lain = str_replace(',', '', $this->post('total_lain'));
            $total_closing = str_replace(',', '', $this->post('total_closing'));
            $result = $auth['data'];
            $id_user = $result->id;
            $user = $result->username;
            $create_date = date('Y-m-d H:i:s');

            $cekTotalSistem = $this->model->getTotalSistemToday($tanggal);
            $total_sistem = $cekTotalSistem['TOTAL'];
            $selisih = $total_closing - $total_sistem;

            $cekTrxToday = $this->model->cekTrxFinishToday($tanggal);

            if(empty($cekTrxToday)){
                $this->response(
                    array('message' => 'Tidak ada transaksi yang akan ditutup', 'error' => 404), 
                    REST_Controller::HTTP_NOT_FOUND
                );
                return;
            }
            
            $update = $this->model->updateStatusClosing($tanggal);

            if($update){
                $this->model->updateStatusClosingDetailTrx($tanggal);

                $value = array(
                    'TANGGAL' => $tanggal,
                    'WAKTU' => $waktu,
                    'TOTAL_TUNAI' => $total_tunai,
                    'TOTAL_KARTU' => $total_kartu,
                    'TOTAL_PENGELUARAN' => $total_pengeluaran,
                    'TOTAL_LAIN' => $total_lain,
                    'TOTAL' => $total_closing,
                    'TOTAL_SISTEM' => $total_sistem,
                    'SELISIH' => $selisih,
                    'ID_USER' => $id_user,
                    'USER' => $user,
                    'CREATE_DATE' => $create_date
                );
                $this->model->insertClosing($value);

                $this->response(
                    array('success' => 200, 'message' => 'Transaksi berhasil ditutup', 'closing' => $value, 'result' => $cekTrxToday),
                    REST_Controller::HTTP_OK
                );
                return;
            }else{
                $this->response(
                    array('message' => 'Gagal menutup kasir', 'error' => 400), 
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