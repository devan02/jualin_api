<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Auth extends REST_Controller
{
	public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");
        $this->load->model('auth_m', 'model');
        // Load Authorization Library or Load in autoload config file
        // $this->load->library('authorization_token');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD, OPTIONS");
    }

    public function register_post()
    {
    	$username = $this->post('username');
    	$password = $this->post('password');
    	$level = $this->post('level');
    	$createDate = date('Y-m-d H:i:s');

        // username wajib diisi
        if($username == "" || $username == null){ 
            $this->response(
                array(
                    'message' => 'Username tidak boleh kosong',
                    'errorKey' => 'validateUsername',
                    'error' => 400
                ), REST_Controller::HTTP_BAD_REQUEST
            );
            return;
        }
        // password wajib diisi
        if($password == "" || $password == null){ 
            $this->response(
                array(
                    'message' => 'Password tidak boleh kosong',
                    'errorKey' => 'validatePassword',
                    'error' => 400
                ), REST_Controller::HTTP_BAD_REQUEST
            );
            return;
        }
        //cek username exist
        $cekUsername = $this->model->getUserByUsername($username);
        if(count($cekUsername) > 0){
        	$this->response(
                array(
                    'message' => 'Username '.$username.' sudah ada',
                    'errorKey' => 'existUsername',
                    'error' => 400
                ), REST_Controller::HTTP_BAD_REQUEST
            );
            return;
        }

        $value = array(
			'USERNAME' => $username,
			'PASSWORD' => password_hash($password, PASSWORD_DEFAULT),
			'LEVEL' => $level,
            'AKTIF' => 1,
			'CREATEDATE' => $createDate
        );

        $insertUser = $this->db->insert('user', $value);

        if(!isset($insertUser)){
            $this->response(
            	array(
            		'message' => 'Failed registrasi', 
            		'errorKey' => 'failedRegistrasi',
            		'error' => 502
            	), 
            	REST_Controller::HTTP_BAD_GATEWAY
        	);
        }else{
        	//jika berhasil registrasi, get data sesuai username
        	$reg = $this->model->getUserByUsername($username);
            $this->response(
    			array('success' => 200, 'result' => $reg),
    			REST_Controller::HTTP_OK
    		);
            return;
        }
    }

    public function login_post()
    {
    	$username = $this->post('username');
    	$password = $this->post('password');

    	// username wajib diisi
        if($username == "" || $username == null){ 
            $this->response(
                array(
                    'message' => 'Username tidak boleh kosong',
                    'errorKey' => 'validateUsername',
                    'error' => 400
                ), REST_Controller::HTTP_BAD_REQUEST
            );
            return;
        }
        // password wajib diisi
        if($password == "" || $password == null){ 
            $this->response(
                array(
                    'message' => 'Password tidak boleh kosong',
                    'errorKey' => 'validatePassword',
                    'error' => 400
                ), REST_Controller::HTTP_BAD_REQUEST
            );
            return;
        }

        //cek username
        $cekUsername = $this->model->getUserByUsername($username);
        if(count($cekUsername) == 0){
        	$this->response(
                array(
                    'message' => 'Username '.$username.' tidak ada',
                    'errorKey' => 'wrongUsername',
                    'error' => 400
                ), REST_Controller::HTTP_BAD_REQUEST
            );
            return;
        }

        if(count($cekUsername) > 0){
        	$login = $this->model->getDataUserRow($username);

        	if(!password_verify($password, $login['PASSWORD'])){
                $this->response(
                    array(
                        'message' => 'Password Anda salah', 
                        'errorKey' => 'wrongPassword',
                        'error' => 401
                    ), REST_Controller::HTTP_UNAUTHORIZED
                );
                return;
            }

            header("Access-Control-Allow-Origin: *");
            // header("Access-Control-Allow-Credentials: true");
            // header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD, OPTIONS");

            // $cors = $this->_check_cors();
            
            // $this->response($cors,REST_Controller::HTTP_OK);
            // return;

            // API Configuration
            $this->_apiConfig([
                'methods' => ['POST'],
            ]);

            // you user authentication code will go here, you can compare the user with the database or whatever
            $payload = [
                'id' => $login['ID'],
                'username' => $username,
                'level' => $login['LEVEL']
            ];

            // Load Authorization Library or Load in autoload config file
            // $this->load->library('Authorization_Token');

            // generte a token
            $token = $this->authorization_token->generateToken($payload);            
            
            //update token setelah login
            $value = array(
                'TOKEN' => $token
            );

            $this->model->updateTokenLogin($username, $value);
            $dataToken = $this->model->getDataUserRow($username);
            $dataUser = $this->model->getDataUserLimit($username);

            $this->response(
                array(
                	'success' => 200,
                    'token' => $dataToken['TOKEN'],
                	'result' => $dataUser
                ), 
                REST_Controller::HTTP_OK
            );
        }else{
        	$this->response(
                array(
                    'message' => 'Pastikan username dan password Anda benar', 
                    'errorKey' => 'unauthorized',
                    'error' => 401
                ), REST_Controller::HTTP_UNAUTHORIZED
            );
            return;
        }
    }

    public function logout_post()
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
	    	$username = $this->post('username');
	    	$value = array(
	    		'LAST_LOG' => date('Y-m-d H:i:s'),
                'TOKEN' => null
	    	);

	    	$this->db->where('USERNAME',$username);
	        $put = $this->db->update('user', $value);

	        if($put){
	        	$this->response(
	                array('success' => 200, 'message' => 'Logout Success'), 
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
                ), 
                REST_Controller::HTTP_UNAUTHORIZED);
            return;
    	}
    }

    public function me_get()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $result = $auth['data'];
            $id = $result->id;

            $data = $this->model->getDataUserById($id);

            $this->response(
                array(
                    'success' => 200,
                    'auth' => $auth,
                    'result' => $data
                ), 
                REST_Controller::HTTP_OK
            );
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

    function check_auth_get(){
        $auth = $this->authorization_token->validateToken();

        if(!$auth['status']){
            $this->response(
                array(
                    'data' => $auth, 
                    'errorKey' => 'Unauthorized', 
                    'error' => 401,
                    'message' => 'Token kadaluarsa, mohon tunggu. Sistem akan keluar secara otomatis'
               ), REST_Controller::HTTP_UNAUTHORIZED
            );
            return;   
        }  
    }

    public function testing_get()
    {
        $this->response(
            array(                                
                'success' => 200,
                'message' => 'Connected!'
           ), REST_Controller::HTTP_OK
        );
        return;  
    }
}
?>