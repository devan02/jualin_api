<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Hak_akses extends REST_Controller
{
	public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");
        $this->load->model('hak_akses_m', 'model');
        // Load Authorization Library or Load in autoload config file
        // $this->load->library('authorization_token');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD, OPTIONS");
    }

    function menu1_get(){
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
    		$result = $auth['data'];

    		if($result->level == 'Admin' || $result->level == 'Super Admin'){
	    		$id = $this->get('id');
	    		$status = $this->get('status');
	    		// $page = $this->get('page');
	      //       $limit = $this->get('limit');
	            $metadata = $this->get('metadata');

	            $data = $this->model->getMenuSatu($id, $status);

	            if($id != ""){
	            	$this->response(
	                    array('result' => $data, 'success' => 200),
	                    REST_Controller::HTTP_OK
	                );
	                return;
	            }else{
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

    function menu1_post()
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
    		$result = $auth['data'];

    		if($result->level == 'Admin' || $result->level == 'Super Admin'){
	    		$urut = $this->post('urut');
	    		$view = $this->post('view');
	    		$nama = $this->post('nama');
	    		$link = $this->post('link');
	    		$class = $this->post('class');
	    		$master = 'Toko';
	    		$status = 'actived';

	    		if($urut == ""){
	    			$this->response(
	    				array('message' => 'Urutan menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		if($view == ""){
	    			$this->response(
	    				array('message' => 'View menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		if($nama == ""){
	    			$this->response(
	    				array('message' => 'Nama menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		if($class == ""){
	    			$this->response(
	    				array('message' => 'Class menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		$cekDataByName = $this->model->getDataByFilter($nama, '');
	    		if(count($cekDataByName) > 0){
	    			$this->response(
	    				array('message' => 'Menu '.$nama.' sudah ada', 'error' => 400), 
	    				REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

				$cekDataByUrut = $this->model->getDataByFilter('', $urut);
	    		if(count($cekDataByUrut) > 0){
	    			foreach ($cekDataByUrut as $key => $value) {
		    			$this->response(
		    				array('message' => 'Urutan '.$urut.' sudah ada di Menu '.$value->NAMA_MENU, 'error' => 400), 
		    				REST_Controller::HTTP_BAD_REQUEST
		    			);	    				
	    			}
	                return;
	    		}	    		

	    		$value = array(
	    			'URUT' => $urut,
					'VIEW' => $view,
					'NAMA_MENU' => $nama,
					'LINK' => $link,
					'CLASS' => $class,
					'MASTER_APLIKASI' => $master,
					'STATUS' => $status,
	    		);
	    		$insert = $this->model->postData($value);

	    		if(!isset($insert)){
					$this->response(array('message' => $value, 'error' => 400), REST_Controller::HTTP_BAD_REQUEST);
	                return;
				}else{
	                $data = $this->model->getDataAfterInsert();

	                $this->response(
	                    array('success' => 201, 'message' => 'Data berhasil disimpan', 'result' => $data),
	                    REST_Controller::HTTP_CREATED
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

    function menu1_put()
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
    		$result = $auth['data'];

    		if($result->level == 'Admin' || $result->level == 'Super Admin'){
	    		$id = $this->put('id');

	    		if($id != ""){
	    			$urut = $this->put('urut');
		    		$view = $this->put('view');
		    		$nama = $this->put('nama');
		    		$link = $this->put('link');
		    		$class = $this->put('class');
		    		$status = $this->put('status');

		    		if($urut == ""){
		    			$this->response(
		    				array('message' => 'Urutan menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
		    			);
		                return;
		    		}

		    		if($view == ""){
		    			$this->response(
		    				array('message' => 'View menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
		    			);
		                return;
		    		}

		    		if($nama == ""){
		    			$this->response(
		    				array('message' => 'Nama menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
		    			);
		                return;
		    		}

		    		if($link == ""){
		    			$this->response(
		    				array('message' => 'Link menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
		    			);
		                return;
		    		}

		    		if($class == ""){
		    			$this->response(
		    				array('message' => 'Class menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
		    			);
		                return;
		    		}

		    		if($status == ""){
		    			$this->response(
		    				array('message' => 'Status menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
		    			);
		                return;
		    		}

		    		$arrStatus = array('actived', 'deactived');
		    		if(!in_array($status, $arrStatus)){
		    			$this->response(
		    				array('message' => 'Pilih status antara actived atau deactived', 'error' => 400), 
		    				REST_Controller::HTTP_BAD_REQUEST
		    			);
		                return;
		    		}

		    		$cekMenuBeforeUpdate = $this->model->getData($id, '');
		    		if(empty($cekMenuBeforeUpdate)){
		    			$this->response(
		    				array('message' => 'Menu tidak ada, pastikan ID menu ada', 'error' => 404), REST_Controller::HTTP_NOT_FOUND
		    			);
		                return;
		    		}		    			    		

		    		$value = array(
		    			'URUT' => $urut,
						'VIEW' => $view,
						'NAMA_MENU' => $nama,
						'LINK' => $link,
						'CLASS' => $class,
						'STATUS' => $status,
		    		);
		    		
		    		$this->db->where('ID', $id);
		    		$this->db->update('adm_menu_1', $value);
		    		$data = $this->model->getData($id, '');

		    		$this->response(
	                    array('success' => 200, 'message' => 'Menu berhasil diubah', 'result' => $data),
	                    REST_Controller::HTTP_OK
	                );
	                return;
	    		}else{
	    			$this->response(
		                array('message' => 'ID menu tidak ada', 'error' => 400), 
		                REST_Controller::HTTP_BAD_REQUEST
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

    function menu1_delete($id="")
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
    		$result = $auth['data'];

    		if($result->level == 'Admin' || $result->level == 'Super Admin'){
    			if($id != ""){
    				if($id == 0 || $id == '0'){
    					$this->response(
		    				array('message' => 'ID tidak boleh 0', 'error' => 400),
		    				REST_Controller::HTTP_BAD_REQUEST
		    			);
		                return;
    				}

    				$idOnlyNumber = preg_replace('/[^0-9]/', '', $id);
    				if(!$idOnlyNumber){
    					$this->response(
		    				array('message' => 'ID harus angka', 'error' => 400),
		    				REST_Controller::HTTP_BAD_REQUEST
		    			);
		                return;
    				}

    				$cekMenuBeforeDelete = $this->model->getData($id, '');
		    		if(empty($cekMenuBeforeDelete)){
		    			$this->response(
		    				array('message' => 'Menu tidak ada, pastikan ID menu ada', 'error' => 404),
		    				REST_Controller::HTTP_NOT_FOUND
		    			);
		                return;
		    		}

		    		$cekMenuDuaBeforeDelete = $this->model->getMenuDuaByIdMenuSatu($id);
		    		if(count($cekMenuDuaBeforeDelete) > 0){
		    			$dataMenu1 = $this->model->getData($id, '');

		    			$this->response(
		    				array(
		    					'message' => 'Menu tidak bisa dihapus, hapus dulu sub menu '.$dataMenu1['NAMA_MENU'], 
		    					'error' => 400,
		    					'result' => $cekMenuDuaBeforeDelete
		    				),
		    				REST_Controller::HTTP_BAD_REQUEST
		    			);
		                return;
		    		}

		    		$this->db->where('ID', $id);
		    		$this->db->delete('adm_menu_1');

		    		$this->response(
	                    array('success' => 200, 'message' => 'Data berhasil dihapus'),
	                    REST_Controller::HTTP_OK
	                );
	                return;
    			}else{
    				$this->response(
	    				array('message' => 'Pastikan ID menu yang akan dihapus', 'error' => 400), 
	    				REST_Controller::HTTP_BAD_REQUEST
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

    // MENU 2
    function menu2_get()
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
    		$result = $auth['data'];

    		if($result->level == 'Admin' || $result->level == 'Super Admin'){
	    		$id = $this->get('id');
	    		$status = $this->get('status');
	    		// $page = $this->get('page');
	      //       $limit = $this->get('limit');
	            $metadata = $this->get('metadata');
	            $id_menu1 = $this->get('id_menu1');

	            $data = $this->model->getMenuDua($id, $id_menu1, $status);

	            if($id != ""){
	            	$this->response(
	                    array('result' => $data, 'success' => 200),
	                    REST_Controller::HTTP_OK
	                );
	                return;
	            }else{
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

    function menu2_post()
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
    		$result = $auth['data'];

    		if($result->level == 'Admin' || $result->level == 'Super Admin'){
	    		$id_menu1 = $this->post('id_menu1');
	    		$urut = $this->post('urut');
	    		$view = $this->post('view');
	    		$nama = $this->post('nama');
	    		$link = $this->post('link');
	    		$class = $this->post('class');
	    		$master = 'Toko';
	    		$status = 'actived';

	    		if($id_menu1 == ""){
	    			$this->response(
	    				array('message' => 'Menu 1 belum dipilih', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		if($urut == ""){
	    			$this->response(
	    				array('message' => 'Urutan menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		if($view == ""){
	    			$this->response(
	    				array('message' => 'View menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		if($nama == ""){
	    			$this->response(
	    				array('message' => 'Nama menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		if($link == ""){
	    			$this->response(
	    				array('message' => 'Link menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		if($class == ""){
	    			$this->response(
	    				array('message' => 'Class menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		$cekDataByName = $this->model->getMenuDuaByFilter($nama, '');
	    		if(count($cekDataByName) > 0){
	    			$this->response(
	    				array('message' => 'Menu '.$nama.' sudah ada', 'error' => 400), 
	    				REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

				$cekDataByUrut = $this->model->getMenuDuaByFilter('', $urut);
	    		if(count($cekDataByUrut) > 0){
	    			foreach ($cekDataByUrut as $key => $value) {
		    			$this->response(
		    				array('message' => 'Urutan '.$urut.' sudah ada di Menu '.$value->NAMA_MENU, 'error' => 400), 
		    				REST_Controller::HTTP_BAD_REQUEST
		    			);	    				
	    			}
	                return;
	    		}	    		

	    		$value = array(
	    			'ID_MENU_1' => $id_menu1,
	    			'URUT' => $urut,
					'VIEW' => $view,
					'NAMA_MENU' => $nama,
					'LINK' => $link,
					'CLASS' => $class,
					'MASTER_APLIKASI' => $master,
					'STATUS' => $status,
	    		);
	    		$insert = $this->model->postMenuDua($value);

	    		if(!isset($insert)){
					$this->response(array('message' => $value, 'error' => 400), REST_Controller::HTTP_BAD_REQUEST);
	                return;
				}else{
	                $data = $this->model->getMenuDuaAfterInsert();

	                $this->response(
	                    array('success' => 201, 'message' => 'Data berhasil disimpan', 'result' => $data),
	                    REST_Controller::HTTP_CREATED
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

    function menu2_put()
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
    		$result = $auth['data'];

    		if($result->level == 'Admin' || $result->level == 'Super Admin'){
	    		$id = $this->put('id');
	    		$id_menu1 = $this->put('id_menu1');
	    		$urut = $this->put('urut');
	    		$view = $this->put('view');
	    		$nama = $this->put('nama');
	    		$link = $this->put('link');
	    		$class = $this->put('class');
	    		$master = 'Toko';
	    		$status = $this->put('status');

	    		if($id == ""){
	    			$this->response(
	    				array('message' => 'ID belum dipilih', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		if($id_menu1 == ""){
	    			$this->response(
	    				array('message' => 'Menu 1 belum dipilih', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		if($urut == ""){
	    			$this->response(
	    				array('message' => 'Urutan menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		if($view == ""){
	    			$this->response(
	    				array('message' => 'View menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		if($nama == ""){
	    			$this->response(
	    				array('message' => 'Nama menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		if($link == ""){
	    			$this->response(
	    				array('message' => 'Link menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		if($class == ""){
	    			$this->response(
	    				array('message' => 'Class menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		if($status == ""){
	    			$this->response(
	    				array('message' => 'Status menu harus diisi', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		$arrStatus = array('actived', 'deactived');
	    		if(!in_array($status, $arrStatus)){
	    			$this->response(
	    				array('message' => 'Pilih status antara actived atau deactived', 'error' => 400), 
	    				REST_Controller::HTTP_BAD_REQUEST
	    			);
	                return;
	    		}

	    		$cekMenuBeforeUpdate = $this->model->getMenuDua($id, '');
	    		if(empty($cekMenuBeforeUpdate)){
	    			$this->response(
	    				array('message' => 'Menu 2 tidak ada, pastikan ID menu ada', 'error' => 404), REST_Controller::HTTP_NOT_FOUND
	    			);
	                return;
	    		}

	    		$value = array(
	    			'ID_MENU_1' => $id_menu1,
	    			'URUT' => $urut,
					'VIEW' => $view,
					'NAMA_MENU' => $nama,
					'LINK' => $link,
					'CLASS' => $class,
					'MASTER_APLIKASI' => $master,
					'STATUS' => $status,
	    		);
	    		
	    		$this->db->where('ID', $id);
	    		$this->db->update('adm_menu_2', $value);
	    		$data = $this->model->getMenuDua($id, '');

	    		$this->response(
                    array('success' => 200, 'message' => 'Data berhasil diubah', 'result' => $data),
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

    function menu2_delete($id="")
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
    		$result = $auth['data'];

    		if($result->level == 'Admin' || $result->level == 'Super Admin'){
    			if($id != ""){
    				if($id == 0 || $id == '0'){
    					$this->response(
		    				array('message' => 'ID tidak boleh 0', 'error' => 400),
		    				REST_Controller::HTTP_BAD_REQUEST
		    			);
		                return;
    				}

    				$idOnlyNumber = preg_replace('/[^0-9]/', '', $id);
    				if(!$idOnlyNumber){
    					$this->response(
		    				array('message' => 'ID harus angka', 'error' => 400),
		    				REST_Controller::HTTP_BAD_REQUEST
		    			);
		                return;
    				}

    				$cekMenuBeforeDelete = $this->model->getMenuDua($id, '');
		    		if(empty($cekMenuBeforeDelete)){
		    			$this->response(
		    				array('message' => 'Menu 2 tidak ada, pastikan ID menu ada', 'error' => 404),
		    				REST_Controller::HTTP_NOT_FOUND
		    			);
		                return;
		    		}

		    		$this->db->where('ID', $id);
		    		$this->db->delete('adm_menu_2');

		    		$this->response(
	                    array('success' => 200, 'message' => 'Data berhasil dihapus'),
	                    REST_Controller::HTTP_OK
	                );
	                return;
    			}else{
    				$this->response(
	    				array('message' => 'Pastikan ID menu yang akan dihapus', 'error' => 400), 
	    				REST_Controller::HTTP_BAD_REQUEST
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