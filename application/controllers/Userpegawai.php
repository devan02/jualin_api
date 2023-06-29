<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Userpegawai extends REST_Controller
{
	public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");
        $this->load->model('Userpegawai_m', 'model');
        $this->load->model('auth_m', 'auth_m');
        // Load Authorization Library or Load in autoload config file
        // $this->load->library('authorization_token');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD, OPTIONS");
    }

    public function datauser_get()
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
    		$result = $auth['data'];

			$meta = $this->get('metadata');
			$keyword = $this->get('keyword');

			$resultData = $this->model->dataUserPegawai($keyword);

			if($resultData){
				$dataCount = 0;
				if($meta == 'true'){
					$dataCount = count($resultData);
				}

    			$this->response(
		            array('success' => 200, 'total_data' => $dataCount, 'result' => $resultData),
		            REST_Controller::HTTP_OK
		        );
    			return;
			}else{
				$this->response(
	                array('message' => 'Data tidak ada', 'error' => 404), 
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

    public function tambahuser_post()
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
    		$result = $auth['data'];

    		if($result->level == 'Admin' || $result->level == 'Super Admin'){
    			$id_pegawai = $this->post('id_pegawai');
    			$username = $this->post('username');
    			$password = $this->post('password');
    			$level = $this->post('level');
    			$createDate = date('Y-m-d H:i:s');

    			// username wajib diisi
		        if($id_pegawai == "" || $id_pegawai == null){ 
		            $this->response(
		                array(
		                    'message' => 'Tidak ada pegawai yang ingin dibuatkan user',
		                    'errorKey' => 'validatePegawai',
		                    'error' => 400
		                ), REST_Controller::HTTP_BAD_REQUEST
		            );
		            return;
		        }
		        //jika pegawai ada
		        $cekPegawai = $this->model->cekUserPegawaiByIdPegawai($id_pegawai);
		        if(count($cekPegawai) > 0){
		        	$dataPegawai = $this->model->cekPegawaiById($id_pegawai)->row_array();

		        	$this->response(
		                array(
		                    'message' => 'Pegawai '.$dataPegawai['NAMA_LENGKAP'].' sudah dibuatkan User',
		                    'errorKey' => 'validateExistPegawai',
		                    'error' => 400
		                ), REST_Controller::HTTP_BAD_REQUEST
		            );
		            return;
		        }
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
		        $cekUsername = $this->auth_m->getUserByUsername($username);
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
		        // level wajib diisi
		        if($level == "" || $level == null){ 
		            $this->response(
		                array(
		                    'message' => 'Level tidak boleh kosong',
		                    'errorKey' => 'validateLevel',
		                    'error' => 400
		                ), REST_Controller::HTTP_BAD_REQUEST
		            );
		            return;
		        }
		        //jika level diluar enum
		        $enumLevel = array('Karyawan', 'Kasir');
		        if(!in_array($level, $enumLevel)){
		        	$this->response(
		                array(
		                    'message' => 'Level hanya boleh Karyawan / Kasir',
		                    'errorKey' => 'validateEnumLevel',
		                    'error' => 405
		                ), REST_Controller::HTTP_METHOD_NOT_ALLOWED
		            );
		            return;
		        }

		        $value = array(
		        	'ID_PEGAWAI' => $id_pegawai, 
		        	'USERNAME' => $username, 
		        	'PASSWORD' => password_hash($password, PASSWORD_DEFAULT),
		        	'LEVEL' => $level,
		        	'AKTIF' => 1,
					'CREATEDATE' => $createDate 
		        );

		        $this->model->tambahUserPegawai($value); // insert data to database

		        $this->response(
			        array('message' => 'User pegawai berhasil dibuat', 'success' => 200),
		            REST_Controller::HTTP_OK
		        );

		        return;
    		}else{
    			$this->response(
	                array('message' => 'Hanya bisa diakses oleh Admin', 'error' => 403), 
	                REST_Controller::HTTP_FORBIDDEN
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

    public function ubahuser_post()
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
    		$result = $auth['data'];

    		if($result->level == 'Admin' || $result->level == 'Super Admin'){
    			$id = $this->post('id');

    			if($id){
    				$id_pegawai = $this->post('id_pegawai');
	    			$username = $this->post('username');
	    			$level = $this->post('level');

	    			//jika id tidak ditemukan
	    			$cekUser = $this->model->cekUserPegawaiById($id);
	    			if(count($cekUser) == 0){
	    				$this->response(
			                array(
			                    'message' => 'User yang dimaksud tidak ada',
			                    'errorKey' => 'validateUser',
			                    'error' => 404
			                ), REST_Controller::HTTP_NOT_FOUND
			            );
			            return;
	    			}
	    			// id pegawai wajib diisi
			        if($id_pegawai == "" || $id_pegawai == null){ 
			            $this->response(
			                array(
			                    'message' => 'Tidak ada pegawai yang ingin dibuatkan user',
			                    'errorKey' => 'validateIdPegawai',
			                    'error' => 400
			                ), REST_Controller::HTTP_BAD_REQUEST
			            );
			            return;
			        }
			        //jika pegawai tidak ada
			        $cekPegawai = $this->model->cekPegawaiById($id_pegawai);
			        if(count($cekPegawai->result()) == 0){
			        	$this->response(
			                array(
			                    'message' => 'Pegawai yang dicari tidak ada',
			                    'errorKey' => 'validatePegawai',
			                    'error' => 404
			                ), REST_Controller::HTTP_NOT_FOUND
			            );
			            return;
			        }
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
			        // level wajib diisi
			        if($level == "" || $level == null){ 
			            $this->response(
			                array(
			                    'message' => 'Level tidak boleh kosong',
			                    'errorKey' => 'validateLevel',
			                    'error' => 400
			                ), REST_Controller::HTTP_BAD_REQUEST
			            );
			            return;
			        }
			        //jika level diluar enum
			        $enumLevel = array('Karyawan', 'Kasir');
			        if(!in_array($level, $enumLevel)){
			        	$this->response(
			                array(
			                    'message' => 'Level hanya boleh Karyawan / Kasir',
			                    'errorKey' => 'validateEnumLevel',
			                    'error' => 405
			                ), REST_Controller::HTTP_METHOD_NOT_ALLOWED
			            );
			            return;
			        }
			        //cek username
			        $cekUsername = $this->model->cekUserPegawaiByIdAndIdPegawai($id, $id_pegawai);

			        if(count($cekUsername->result()) > 0){ //jika user ada
			        	$dataUser = $cekUsername->row_array(); //ambil data user

			        	if($dataUser['USERNAME'] == $username){ //username tidak diubah
			        		$value = array(
					        	'ID_PEGAWAI' => $id_pegawai, 
					        	'LEVEL' => $level,
					        	'LASTMODIFY' => date('Y-m-d H:i:s')
					        );

				        	$this->model->ubahUserPegawai($id,$value); // update data to database

					        $this->response(
						        array('message' => 'Level pegawai berhasil diubah', 'success' => 200),
					            REST_Controller::HTTP_OK
					        );

					        return;
			        	}

			        	if($dataUser['USERNAME'] != $username){ // username diubah
			        		//cek username exist
					        $cekUsername = $this->auth_m->getUserByUsername($username);
					        if(count($cekUsername) > 0){
					        	$this->response(
					                array(
					                    'message' => 'Username '.$username.' sudah ada',
					                    'errorKey' => 'existUsername',
					                    'error' => 400
					                ), REST_Controller::HTTP_BAD_REQUEST
					            );
					            return;
					        }else{
					        	$value = array(
						        	'ID_PEGAWAI' => $id_pegawai, 
						        	'USERNAME' => $username, 
						        	'LEVEL' => $level,
						        	'LASTMODIFY' => date('Y-m-d H:i:s')
						        );

						        $this->model->ubahUserPegawai($id,$value); // update data to database

						        $this->response(
							        array('message' => 'User pegawai berhasil diubah', 'success' => 200),
						            REST_Controller::HTTP_OK
						        );

						        return;
					        }
			        	}		        	
			        }
    			}else{
    				$this->response(
		                array('message' => 'Terjadi kesalahan data, pastikan data terisi dengan benar', 'error' => 403), 
		                REST_Controller::HTTP_FORBIDDEN
		            );
		            return;
    			}    			
    		}else{
    			$this->response(
	                array('message' => 'Hanya bisa diakses oleh Admin', 'error' => 403), 
	                REST_Controller::HTTP_FORBIDDEN
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

    public function aktifnonaktifuser_post()
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
    		$result = $auth['data'];

    		if($result->level == 'Admin' || $result->level == 'Super Admin'){
    			$id = $this->post('id');
    			$aktif = $this->post('status');

    			if(preg_replace("/[^0-9]/", "",$id)){
    				// if($aktif == "" || $aktif == null){
    				// 	$this->response(
			     //            array(
			     //                'message' => 'Silahkan pilih 0 (nonaktif) atau 1 (aktifkan) User',
			     //                'errorKey' => 'userActivation',
			     //                'error' => 400
			     //            ), REST_Controller::HTTP_BAD_REQUEST
			     //        );
			     //        return;
    				// }

    				// if(!preg_replace("/[^0-9]/", "",$aktif)){
    				// 	$this->response(
			     //            array(
			     //                'message' => 'Inputan harus angka / number',
			     //                'errorKey' => 'mustNumberActivation',
			     //                'error' => 403
			     //            ), REST_Controller::HTTP_METHOD_NOT_ALLOWED
			     //        );
			     //        return;
    				// }

    				// if($aktif > 1){
    				// 	$this->response(
			     //            array(
			     //                'message' => 'Silahkan pilih 0 (nonaktif) atau 1 (aktifkan) User',
			     //                'errorKey' => 'userActivation',
			     //                'error' => 403
			     //            ), REST_Controller::HTTP_METHOD_NOT_ALLOWED
			     //        );
			     //        return;
    				// }

    				// if($aktif < 0){
    				// 	$this->response(
			     //            array(
			     //                'message' => 'Inputan tidak boleh minus',
			     //                'errorKey' => 'notMinusActivation',
			     //                'error' => 403
			     //            ), REST_Controller::HTTP_METHOD_NOT_ALLOWED
			     //        );
			     //        return;
    				// }

    				$notif = "";
    				if($aktif == 0){ //nonaktif
    					$notif = "dinonaktifkan";
    				}else{
    					$notif = "diaktifkan";
    				}

    				$value = array(
			        	'AKTIF' => $aktif,
			        	'LASTMODIFY' => date('Y-m-d H:i:s')
			        );

			        $this->model->ubahUserPegawai($id,$value); // update data to database

			        $this->response(
				        array('message' => 'User pegawai berhasil '.$notif, 'success' => 200),
			            REST_Controller::HTTP_OK
			        );

			        return;
    			}else{
    				$this->response(
		                array('message' => 'Terjadi kesalahan data, pastikan data terisi dengan benar', 'error' => 403), 
		                REST_Controller::HTTP_FORBIDDEN
		            );
		            return;
    			}
    		}else{
    			$this->response(
	                array('message' => 'Hanya bisa diakses oleh Admin', 'error' => 403), 
	                REST_Controller::HTTP_FORBIDDEN
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