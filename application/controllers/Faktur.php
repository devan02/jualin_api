<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Faktur extends REST_Controller
{
	public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");
        $this->load->model('faktur_m', 'model');
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

    function no_faktur_kirim_get()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            # code... FK-202204001
            $nomor = $this->nomor_m->get_nomor_faktur_kirim('FAKTUR-KIRIM');
            $kode = 'FK-'.date('Y').date('m').$this->add_leading_zero($nomor);
            $arrayName = array('no_faktur_kirim' => $kode);

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

    function faktur_kirim_get(){
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
    		$data = $this->model->getFakturAfterInsert();   
    		$detail = "";

    		if($data){
    			$no = $data['NO_FAKTUR'];
    			$detail = $this->model->getDetailFakturAfterInsert($no);
    		}            

            $this->response(
                array('success' => 200, 'result' => $data, 'detail' => $detail),
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

    }

    function index_post()
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
    		$no_faktur = $this->post('no_faktur');
            $tgl = $this->post('tanggal');
            $total_qty = $this->post('total_item');
            $total_harga = $this->post('total_harga');
            $tipe = $this->post('tipe');
            $createDate = date('Y-m-d H:i:s');

            $value = array(                
                'NO_FAKTUR' => $no_faktur,
                'TANGGAL' => $tgl,
                'TOTAL_HARGA' => $total_harga,
                'TOTAL_ITEM' => $total_qty,
                'TIPE' => $tipe,
                'CREATE_DATE' => $createDate
            );

            $insert = $this->model->postFakturKirim($value);

            if(!isset($insert)){
                $this->response(array('message' => 'Gagal menambah data', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST);
                return;
            }else{
                $this->nomor_m->check_nomor_faktur_kirim('FAKTUR-KIRIM'); // nomor auto
                $data = $this->model->getFakturAfterInsert();               

                $this->response(
                    array('success' => 201, 'message' => 'Faktur berhasil disimpan', 'result' => $data),
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

    function detail_faktur_kirim_post()
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
    		$no_faktur = $this->post('no_faktur');
            $id_barang = $this->post('id_barang');
            $plu = $this->post('plu');
            $harga_beli = str_replace(',', '', $this->post('harga_beli'));
            $harga_jual = str_replace(',', '', $this->post('harga_jual'));
            $qty = $this->post('qty');
            $subtotal = str_replace(',', '', $this->post('subtotal'));
            $createDate = date('Y-m-d H:i:s');

            $value = array(
                'NO_FAKTUR' => $no_faktur,
                'ID_BARANG' => $id_barang,
                'PLU' => $plu,
                'HARGA_BELI' => $harga_beli,
                'HARGA_JUAL' => $harga_jual,
                'QTY' => $qty,
                'SUBTOTAL' => $subtotal,
                'CREATE_DATE' => $createDate
            );

            $insert = $this->model->postDetailFakturKirim($value);

            if(!isset($insert)){
                $this->response(array('message' => 'Gagal menambah data', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST);
                return;
            }else{
            	//kondisi stok barang
                $cekBarang = $this->barang_m->getDetailBarang($id_barang);

                if($cekBarang['STOK_SAAT_INI'] > 0){
                    $stokLama = $cekBarang['STOK_SAAT_INI'];
                    $stokNow = $cekBarang['STOK_SAAT_INI'] - $qty;
                    $sql = "UPDATE adm_barang SET STOK_LAMA = $stokLama, STOK_SAAT_INI = $stokNow WHERE ID = $id_barang";
                    $this->db->query($sql);
                }

                $data = $this->model->getDetailFakturAfterInsert($no_faktur);
                $this->response(
                    array('success' => 201, 'message' => 'Faktur berhasil disimpan', 'result' => $data),
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
}
?>