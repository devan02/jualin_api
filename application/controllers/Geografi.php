<?php use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Geografi extends REST_Controller
{
	public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");
        $this->load->model('geografi_m', 'model');
        // Load Authorization Library or Load in autoload config file
        // $this->load->library('authorization_token');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD, OPTIONS");
    }

    function provinsi_get(){
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){ // jika token ada
            $metadata = $this->get('metadata');

            $data = $this->model->getProvinsi();

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

    function kota_kab_get(){
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){ // jika token ada
            $metadata = $this->get('metadata');
            $provinsi = $this->get('provinsi');

            $data = $this->model->getKotaKabByProvinsi($provinsi);

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

    function kecamatan_get(){
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){ // jika token ada
            $metadata = $this->get('metadata');
            $kota_kab = $this->get('kota_kab');

            $data = $this->model->getKecamatanByKotaKab($kota_kab);

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

    function kelurahan_get(){
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){ // jika token ada
            $metadata = $this->get('metadata');
            $kecamatan = $this->get('kecamatan');

            $data = $this->model->getKelurahanByKecamatan($kecamatan);

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

    function kodepos_get(){
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){ // jika token ada
            $metadata = $this->get('metadata');
            $kelurahan = $this->get('kelurahan');
            $kecamatan = $this->get('kecamatan');

            $data = $this->model->getKodeposByKelurahan($kelurahan, $kecamatan);

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

}