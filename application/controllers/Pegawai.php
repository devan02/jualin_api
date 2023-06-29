<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Pegawai extends REST_Controller
{
	public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");
        $this->load->model('pegawai_m', 'model');
        // Load Authorization Library or Load in autoload config file
        // $this->load->library('authorization_token');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD, OPTIONS");
    }

    public function data_get()
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){ // jika token ada
    		$result = $auth['data'];

			$meta = $this->get('metadata');
			$id = $this->get('id');
			$filter = $this->get('filter');
			$keyword = $this->get('keyword');

			$result = $this->model->dataPegawai($id,$filter,$keyword);

			if($result){				
				if($meta == 'true'){
	    			$this->response(
			            array('success' => 200, 'total_data' => count($result), 'result' => $result),
			            REST_Controller::HTTP_OK
			        );
	    			return;
				}else{
					$this->response(
			            array('success' => 200, 'result' => $result),
			            REST_Controller::HTTP_OK
			        );
	    			return;
				}
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
                ), 
                REST_Controller::HTTP_UNAUTHORIZED);
            return;
    	}
    }

    public function tambah_post()
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){ // jika token ada
    		$result = $auth['data'];

    		if($result->level == 'Admin' || $result->level == 'Super Admin'){
    			$nip = $this->post('nip');
    			$nama = $this->post('nama');
    			$ttl = $this->post('tanggal_lahir');
    			$jk = $this->post('jenis_kelamin');
    			$telepon = $this->post('telepon');
    			$email = $this->post('email');
    			$status = $this->post('status');
    			$alamat = $this->post('alamat');
    			$provinsi = $this->post('provinsi');
    			$kota = $this->post('kota');
    			$kecamatan = $this->post('kecamatan');
    			$kelurahan = $this->post('kelurahan');
    			$kode_pos = $this->post('kode_pos');
    			$foto = $this->post('foto');
    			$folder_path = '/assets/images/foto_pegawai/'; //set folder upload

    	// 		$filename = $_FILES['foto']['name'];
		 		// $tempPath = $_FILES['foto']['tmp_name'];
		 		// $filesize = $_FILES['foto']['size'];

    			//jika nip kosong
    			if($nip == "" || $nip == null){
    				$this->response(array('message' => 'NIP tidak boleh kosong','error' => 400, 'validate' => 'nip'), REST_Controller::HTTP_BAD_REQUEST);
		            return;
    			}
    			//jika nama kosong
    			if($nama == "" || $nama == null){
    				$this->response(array('message' => 'Nama tidak boleh kosong','error' => 400, 'validate' => 'nama'), REST_Controller::HTTP_BAD_REQUEST);
		            return;
    			}
    			
    			//-- VALIDASI TANGGAL LAHIR & UMUR --// 01-07-2022
    			//jika tanggal lahir kosong
    			if($ttl == "" || $ttl == null){
    				$this->response(array('message' => 'Tanggal lahir tidak boleh kosong','error' => 400, 'validate' => 'ttl'), REST_Controller::HTTP_BAD_REQUEST);
		            return;
    			}
    			//jika umur < 17 tahun
    			$yearNow = date('Y');
    			$ttlInput = str_replace('-', '', $ttl);
            	$yearInput = substr($ttlInput, 4);
            	$umur = (int)$yearNow - (int)$yearInput;
            	if($umur < 17){
            		$this->response(array('message' => 'Pastikan umur karyawan diatas 17 tahun', 'umur' => $umur, 'error' => 400, 'validate' => 'umur'), REST_Controller::HTTP_BAD_REQUEST);
		            return;
            	}

    			//jika jenis kelamin kosong
    			if($jk == "" || $jk == null){
    				$this->response(array('message' => 'Jenis kelamin tidak boleh kosong','error' => 400, 'validate' => 'jk'), REST_Controller::HTTP_BAD_REQUEST);
		            return;
    			}

    			//-- VALIDASI TELEPON --// 08
    			//jika telepon kosong
    			if($telepon == "" || $telepon == null){
    				$this->response(array('message' => 'Nomor telepon tidak boleh kosong','error' => 400, 'validate' => 'telepon'), REST_Controller::HTTP_BAD_REQUEST);
		            return;
    			}
    			//jika angka depan tidak 0
    			$telpNol = substr($telepon, 0,1);
    			if($telpNol != 0){
    				$this->response(array('message' => 'Angka depan telepon harus 0','error' => 400, 'validate' => 'telepon_nol'), REST_Controller::HTTP_BAD_REQUEST);
		            return;
    			}
    			//jika digit ke 2 telepon bukan 8
    			$telpDelapan = substr($telepon, 1,1);
    			if($telpDelapan != 8){
    				$this->response(array('message' => 'Digit ke 2 telepon harus 8', 'error' => 400, 'digit 2' => $telpDelapan, 'validate' => 'telepon_delapan'), REST_Controller::HTTP_BAD_REQUEST);
		            return;
    			}

    			// -- VALIDASI EMAIL -- //
    			//jika email kosong
    			if($email == "" || $email == null){
    				$this->response(array('message' => 'Nomor email tidak boleh kosong','error' => 400, 'validate' => 'email_empty'), REST_Controller::HTTP_BAD_REQUEST);
		            return;
    			}
    			//jika email tidak valid
    			if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {    				
				 	$this->response(array('message' => 'Email tidak valid', 'error' => 400, 'validate' => 'email_not_valid'), REST_Controller::HTTP_BAD_REQUEST);
		            return;    
				}
				//jika email tidak ada di enum
				$enumEmail = array('gmail.com', 'yahoo.com');
				$expEmail = explode('@', $email);
				$domainEmail = $expEmail[1];
				if(!in_array($domainEmail, $enumEmail)){
    				$this->response(array('message' => 'Pastikan domain email benar', 'error' => 400, 'domain' => $domainEmail, 'validate' => 'email_domain'), REST_Controller::HTTP_BAD_REQUEST);
		            return;
    			}

    			//jika alamat kosong
    			if($alamat == "" || $alamat == null){
    				$this->response(array('message' => 'Alamat tidak boleh kosong','error' => 400, 'validate' => 'alamat'), REST_Controller::HTTP_BAD_REQUEST);
		            return;
    			}
    			//jika kelurahan kosong
    			if($kelurahan == "" || $kelurahan == null){
    				$this->response(array('message' => 'Kelurahan tidak boleh kosong','error' => 400, 'validate' => 'kelurahan'), REST_Controller::HTTP_BAD_REQUEST);
		            return;
    			}
    			//jika kecamatan kosong
    			if($kecamatan == "" || $kecamatan == null){
    				$this->response(array('message' => 'Kecamatan tidak boleh kosong','error' => 400, 'validate' => 'kecamatan'), REST_Controller::HTTP_BAD_REQUEST);
		            return;
    			}
    			//jika kota kosong
    			if($kota == "" || $kota == null){
    				$this->response(array('message' => 'Kota tidak boleh kosong','error' => 400, 'validate' => 'kota'), REST_Controller::HTTP_BAD_REQUEST);
		            return;
    			}
    			//jika provinsi kosong
    			if($provinsi == "" || $provinsi == null){
    				$this->response(array('message' => 'Provinsi tidak boleh kosong','error' => 400, 'validate' => 'prov'), REST_Controller::HTTP_BAD_REQUEST);
		            return;
    			}
    			//jika kode_pos kosong
    			if($kode_pos == "" || $kode_pos == null){
    				$this->response(array('message' => 'Kode pos tidak boleh kosong','error' => 400, 'validate' => 'kode_pos'), REST_Controller::HTTP_BAD_REQUEST);
		            return;
    			}

    			//jika nip sudah ada
    			$cekNip = $this->model->cekNip($nip);
    			if($cekNip != null){
					$this->response(array('message' => 'NIP '.$nip.' sudah dipakai '.$cekNip['NAMA_LENGKAP'], 'error' => 400, 'validate' => 'nip'), REST_Controller::HTTP_BAD_REQUEST);
					return;
    			}

    			$value = array(
					'TANGGAL_MASUK' => date('Y-m-d H:i:s'),
					'NIP' => $nip,
					'NAMA_LENGKAP' => $nama,
					'TANGGAL_LAHIR' => $ttl,
					'JENIS_KELAMIN' => $jk,
					'TELEPON' => $telepon,
					'EMAIL' => $email,
					'STATUS_MENIKAH' => $status,
					'ALAMAT' => $alamat,
					'KELURAHAN' => $kelurahan,
					'KECAMATAN' => $kecamatan,
					'KOTA' => $kota,
					'PROVINSI' => $provinsi,
					'KODE_POS' => $kode_pos,
					'FOTO' => $folder_path.$foto,
					'STATUS' => 'actived',
					'CREATE_DATE' => date('Y-m-d H:i:s')
				);

				$this->model->tambahPegawai($value); // insert data to database
				// move_uploaded_file($tempPath, $folder_path . $filename); // move file from system temporary path to our upload folder path 
				
				$this->response(
		            array('message' => 'Data pegawai tersimpan', 'success' => 200),
		            REST_Controller::HTTP_OK
		        );

				/*
    			if(empty($filename)){ //jika filename kosong
		 			$this->response(
		                array('message' => 'Pilih foto yang akan diupload', 'error' => 400), 
		                REST_Controller::HTTP_BAD_REQUEST
		            );
		            return;
		 		}else{		 			
		 			$fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); //get ext file
		 			$validExt = array('jpg','jpeg','png'); //valid ext

		 			//allow valid ext format
		 			if(!in_array($fileExt, $validExt)){
		 				$this->response(
			                array('message' => 'Jenis file yang boleh diunggah hanya jpg, jpeg, png', 'error' => 400), 
			                REST_Controller::HTTP_BAD_REQUEST
			            );
			            return;
		 			}

		 			// check file size '1MB'
					
					if($filesize > 1000000){
						$this->response(
			                array('message' => 'Ukuran file terlalu besar, max 2 MB', 'error' => 400), 
			                REST_Controller::HTTP_BAD_REQUEST
			            );
			            return;
					}
					
		 			//check file not exist
	 				
	 				if(file_exists($folder_path.$filename)){
	 					$this->response(
			                array('message' => 'File sudah ada', 'error' => 400), 
			                REST_Controller::HTTP_BAD_REQUEST
			            );
			            return;
	 				}	 				
		 		}
		 		*/
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
                ), 
                REST_Controller::HTTP_UNAUTHORIZED);
            return;
    	}
    }

    public function ubah_post()
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){ // jika token ada
    		$result = $auth['data'];

    		if($result->level == 'Admin' || $result->level == 'Super Admin'){
    			$id = $this->post('id');

    			if($id){
	    			$nama = $this->post('nama');
	    			$ttl = $this->post('tanggal_lahir');
	    			$jk = $this->post('jenis_kelamin');
	    			$telepon = $this->post('telepon');
	    			$email = $this->post('email');
	    			$status = $this->post('status');
	    			$alamat = $this->post('alamat');
	    			$provinsi = $this->post('provinsi');
	    			$kota = $this->post('kota');
	    			$kecamatan = $this->post('kecamatan');
	    			$kelurahan = $this->post('kelurahan');
	    			$kode_pos = $this->post('kode_pos');
			 		$foto = $this->post('foto');
			 		$foto_lama = $this->post('foto_lama');
    				$folder_path = '/assets/images/foto_pegawai/'; //set folder upload
    				$foto_fix = "";

    				if(!empty($foto)){
    					$foto_fix = $folder_path.$foto;
    				}else{
    					$foto_fix = $foto_lama;
    				}

	    			//jika nama kosong
	    			if($nama == "" || $nama == null){
	    				$this->response(array('message' => 'Nama tidak boleh kosong','error' => 400, 'validate' => 'nama'), REST_Controller::HTTP_BAD_REQUEST);
			            return;
	    			}
	    			
	    			//-- VALIDASI TANGGAL LAHIR & UMUR --// 01-07-2022
	    			//jika tanggal lahir kosong
	    			if($ttl == "" || $ttl == null){
	    				$this->response(array('message' => 'Tanggal lahir tidak boleh kosong','error' => 400, 'validate' => 'ttl'), REST_Controller::HTTP_BAD_REQUEST);
			            return;
	    			}
	    			//jika umur < 17 tahun
	    			$yearNow = date('Y');
	    			$ttlInput = str_replace('-', '', $ttl);
	            	$yearInput = substr($ttlInput, 4);
	            	$umur = (int)$yearNow - (int)$yearInput;
	            	if($umur < 17){
	            		$this->response(array('message' => 'Pastikan umur karyawan diatas 17 tahun', 'umur' => $umur, 'error' => 400, 'validate' => 'umur'), REST_Controller::HTTP_BAD_REQUEST);
			            return;
	            	}

	    			//jika jenis kelamin kosong
	    			if($jk == "" || $jk == null){
	    				$this->response(array('message' => 'Jenis kelamin tidak boleh kosong','error' => 400, 'validate' => 'jk'), REST_Controller::HTTP_BAD_REQUEST);
			            return;
	    			}

	    			//-- VALIDASI TELEPON --// 08
	    			//jika telepon kosong
	    			if($telepon == "" || $telepon == null){
	    				$this->response(array('message' => 'Nomor telepon tidak boleh kosong','error' => 400, 'validate' => 'telepon'), REST_Controller::HTTP_BAD_REQUEST);
			            return;
	    			}
	    			//jika angka depan tidak 0
	    			$telpNol = substr($telepon, 0,1);
	    			if($telpNol != 0){
	    				$this->response(array('message' => 'Angka depan telepon harus 0','error' => 400, 'validate' => 'telepon_nol'), REST_Controller::HTTP_BAD_REQUEST);
			            return;
	    			}
	    			//jika digit ke 2 telepon bukan 8
	    			$telpDelapan = substr($telepon, 1,1);
	    			if($telpDelapan != 8){
	    				$this->response(array('message' => 'Digit ke 2 telepon harus 8', 'error' => 400, 'digit 2' => $telpDelapan, 'validate' => 'telepon_delapan'), REST_Controller::HTTP_BAD_REQUEST);
			            return;
	    			}

	    			// -- VALIDASI EMAIL -- //
	    			//jika email kosong
	    			if($email == "" || $email == null){
	    				$this->response(array('message' => 'Nomor email tidak boleh kosong','error' => 400, 'validate' => 'email_empty'), REST_Controller::HTTP_BAD_REQUEST);
			            return;
	    			}
	    			//jika email tidak valid
	    			if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {    				
					 	$this->response(array('message' => 'Email tidak valid', 'error' => 400, 'validate' => 'email_not_valid'), REST_Controller::HTTP_BAD_REQUEST);
			            return;    
					}
					//jika email tidak ada di enum
					$enumEmail = array('gmail.com', 'yahoo.com');
					$expEmail = explode('@', $email);
					$domainEmail = $expEmail[1];
					if(!in_array($domainEmail, $enumEmail)){
	    				$this->response(array('message' => 'Pastikan domain email benar', 'error' => 400, 'domain' => $domainEmail, 'validate' => 'email_domain'), REST_Controller::HTTP_BAD_REQUEST);
			            return;
	    			}

	    			//jika alamat kosong
	    			if($alamat == "" || $alamat == null){
	    				$this->response(array('message' => 'Alamat tidak boleh kosong','error' => 400, 'validate' => 'alamat'), REST_Controller::HTTP_BAD_REQUEST);
			            return;
	    			}
	    			//jika kelurahan kosong
	    			if($kelurahan == "" || $kelurahan == null){
	    				$this->response(array('message' => 'Kelurahan tidak boleh kosong','error' => 400, 'validate' => 'kelurahan'), REST_Controller::HTTP_BAD_REQUEST);
			            return;
	    			}
	    			//jika kecamatan kosong
	    			if($kecamatan == "" || $kecamatan == null){
	    				$this->response(array('message' => 'Kecamatan tidak boleh kosong','error' => 400, 'validate' => 'kecamatan'), REST_Controller::HTTP_BAD_REQUEST);
			            return;
	    			}
	    			//jika kota kosong
	    			if($kota == "" || $kota == null){
	    				$this->response(array('message' => 'Kota tidak boleh kosong','error' => 400, 'validate' => 'kota'), REST_Controller::HTTP_BAD_REQUEST);
			            return;
	    			}
	    			//jika provinsi kosong
	    			if($provinsi == "" || $provinsi == null){
	    				$this->response(array('message' => 'Provinsi tidak boleh kosong','error' => 400, 'validate' => 'prov'), REST_Controller::HTTP_BAD_REQUEST);
			            return;
	    			}
	    			//jika kode_pos kosong
	    			if($kode_pos == "" || $kode_pos == null){
	    				$this->response(array('message' => 'Kode pos tidak boleh kosong','error' => 400, 'validate' => 'kode_pos'), REST_Controller::HTTP_BAD_REQUEST);
			            return;
	    			}
	    			
		 			// $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); //get ext file
		 			// $validExt = array('jpg','jpeg','png'); //valid ext		 			

	 				// if(empty($filename)){ //jika filename kosong
		 			// 	$foto = $dataById['FOTO'];
			 		// }else{
			 		// 	//allow valid ext format
			 		// 	if(!in_array($fileExt, $validExt)){
			 		// 		$this->response(
				  //               array('message' => 'Jenis file yang boleh diunggah hanya jpg, jpeg, png', 'error' => 400), 
				  //               REST_Controller::HTTP_BAD_REQUEST
				  //           );
				  //           return;
			 		// 	}

			 		// 	// check file size '1MB'
						// if($filesize > 1000000){
						// 	$this->response(
				  //               array('message' => 'Ukuran file terlalu besar, max 2 MB', 'error' => 400), 
				  //               REST_Controller::HTTP_BAD_REQUEST
				  //           );
				  //           return;
						// }

			 		// 	//check file not exist
		 			// 	if(file_exists($folder_path.$filename)){
		 			// 		$this->response(
				  //               array('message' => 'File sudah ada', 'error' => 400), 
				  //               REST_Controller::HTTP_BAD_REQUEST
				  //           );
				  //           return;
		 			// 	}
		 				

			 		// 	$foto = $folder_path.$filename;
			 		// 	move_uploaded_file($tempPath, $folder_path . $filename); // move file from system temporary path to our upload folder path 
			 		// 	@unlink($dataById['FOTO']);
			 		// }

	    			$dataById = $this->model->dataPegawaiById($id);

	 				$value = array(
						'NAMA_LENGKAP' => $nama,
						'TANGGAL_LAHIR' => $ttl,
						'JENIS_KELAMIN' => $jk,
						'TELEPON' => $telepon,
						'EMAIL' => $email,
						'STATUS_MENIKAH' => $status,
						'ALAMAT' => $alamat,
						'KELURAHAN' => $kelurahan,
						'KECAMATAN' => $kecamatan,
						'KOTA' => $kota,
						'PROVINSI' => $provinsi,
						'KODE_POS' => $kode_pos,
						'FOTO' => $foto_fix,
						'MODIFY_DATE' => date('Y-m-d H:i:s')
					);

					$this->model->ubahPegawai($id,$value); // update data to database
					
					$this->response(
			            array('message' => 'Data pegawai berhasil diubah', 'success' => 200, 'result' => $dataById),
			            REST_Controller::HTTP_OK
			        );
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
                ), 
                REST_Controller::HTTP_UNAUTHORIZED);
            return;
    	}
    }

    public function hapus_post()
    {
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){ // jika token ada
    		$result = $auth['data'];

    		if($result->level == 'Admin' || $result->level == 'Super Admin'){    			
    			$id = $this->post('id');

    			if($id){
    				$this->model->hapusPegawai($id);

    				$this->response(
			            array('message' => 'Data pegawai berhasil dihapus', 'success' => 200),
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
                ), 
                REST_Controller::HTTP_UNAUTHORIZED);
            return;
    	}
    }

    function ubah_status_post(){
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){ // jika token ada
    		$result = $auth['data'];

    		if($result->level == 'Admin' || $result->level == 'Super Admin'){    			
    			$id = $this->post('id');
    			$status = $this->post('status');

    			if($id){
    				$value = array(
    					'STATUS' => $status,
    					'MODIFY_DATE' => date('Y-m-d H:i:s')
    				);

    				$this->model->ubahPegawai($id,$value);

    				$this->response(
			            array('message' => 'Pegawai berhasil diaktifkan', 'success' => 200),
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
                ), 
                REST_Controller::HTTP_UNAUTHORIZED);
            return;
    	}
    }

}
?>