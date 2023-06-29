<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Inventory extends REST_Controller
{
	public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");
        $this->load->model('inventory_m', 'model');
        // Load Authorization Library or Load in autoload config file
        // $this->load->library('authorization_token');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD, OPTIONS");
    }

    function departemen_get()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $bulan = $this->get('bulan');
            $tahun = date('Y');
            $data = $this->model->getReportByDepartement($bulan, $tahun);

            if(!empty($data)){
                $this->response(
                    array('success' => 200, 'total_data' => count($data), 'result' => $data),
                    REST_Controller::HTTP_OK
                );
                return;
            }else{
                $this->response(
                    array('message' => 'Data laporan tidak ditemukan', 'error' => 404), 
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

}