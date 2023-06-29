<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Merchandise extends REST_Controller
{
	public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");
        $this->load->model('merchandise_m', 'model');
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
    		$id_barang = $this->get('id_barang');

    		if($id_barang == "" || $id_barang == 0){
    			$this->response(
                    array('message' => 'Pilih barang yang akan dicek', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
                );
                return;
    		}

    		$data = $this->model->getMerchandiseByIdBarang($id_barang);

    		if(!empty($data)){
    			//RUMUS MU(%)
    			//harga jual - harga beli = hasil
				//(hasil / harga beli) * 100%
    			
    			$harga_jual = $data['HARGA_JUAL'];
    			$harga_beli = $data['HARGA_BELI'];
    			$hasil = $harga_jual - $harga_beli;
    			$mu = ($hasil / $harga_beli) * 100;

    			$dataAvg = $this->model->getAvgFromRcvDetailByIdBarang($id_barang);
    			$avg = $dataAvg['AVG_HARGA_BELI'];

    			$dataLastSold = $this->model->getLastSoldByIdBarang($id_barang);
    			$lastSold = $dataLastSold ? $dataLastSold['LAST_SOLD'] : '-';

    			$tgl_awal = $this->get('tanggal_awal');
    			$tgl_akhir = $this->get('tanggal_akhir');

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

    			$dataRcv = $this->model->getRcvDetailByIdBarang($id_barang, $tgl_awal, $tgl_akhir, $tanggal_awal, $tanggal_akhir);

    			$this->response(
                    array(
                    	'success' => 200,
                    	'avg' => $avg,
                    	'mu' => number_format($mu, 2),
                    	'last_sold' => $lastSold,
                    	'result' => $data,
                    	'receive' => $dataRcv
                    ),
                    REST_Controller::HTTP_OK
                );
                return;
    		}else{
    			$this->response(
                    array('message' => 'Barang yang dicari tidak ada', 'error' => 404), REST_Controller::HTTP_NOT_FOUND
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