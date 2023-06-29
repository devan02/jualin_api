<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Upload extends REST_Controller
{
	public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");
        $this->load->model('supplier_m', 'model');
        // Load Authorization Library or Load in autoload config file
        // $this->load->library('authorization_token');
        header("Content-Type: application/json");
		header("Access-Control-Allow-Headers: Acess-Control-Allow-Headers,Content-Type,Acess-Control-Allow-Methods, Authorization");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD, OPTIONS");
    }

    private function set_upload_options(){   
		//upload an image options
		$config = array();
		$config['upload_path'] = './files/image/';
		$config['allowed_types'] = '*';
		$config['max_size']      = '0';
		$config['overwrite']     = FALSE;

		return $config;
	}

	function upload($file){
		$this->load->library('upload');
		$files = $_FILES;
		if(isset($_FILES[$file])){
		   $_FILES[$file]['name'] = str_replace(' ', '_', $files[$file]['name']);
		   $_FILES[$file]['type'] = $files[$file]['type'];
		   $_FILES[$file]['tmp_name'] = $files[$file]['tmp_name'];
		   $_FILES[$file]['error'] = $files[$file]['error'];
		   $_FILES[$file]['size'] = $files[$file]['size'];    

		   $this->upload->initialize($this->set_upload_options()); //memanggil fungsi untuk upload path
		   $this->upload->do_upload($file);
		}
	}

    public function fotopegawai_post()
    {
 		$data = json_decode(file_get_contents("php://input"), true);

 		$filename = $_FILES['file']['name'];
 		$tempPath = $_FILES['file']['tmp_name'];
 		$filesize = $_FILES['file']['size'];

 		if(empty($filename)){ //jika filename kosong
 			$this->response(
                array('message' => 'Pilih file yang akan diupload', 'error' => 400), 
                REST_Controller::HTTP_BAD_REQUEST
            );
            return;
 		}else{
 			$folder_path = './assets/images/foto_pegawai/'; //set folder upload
 			$fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); //get ext file
 			$validExt = array('jpg','jpeg','png'); //valid ext

 			//allow valid ext format
 			if(in_array($fileExt, $validExt)){
 				//check file not exist
 				if(!file_exists($folder_path.$filename)){
 					// check file size '2MB'
					if($filesize < 2000000){
						move_uploaded_file($tempPath, $folder_path . $filename); // move file from system temporary path to our upload folder path 
						
						$this->response(
				            array('result' => $filename, 'success' => 200),
				            REST_Controller::HTTP_OK
				        );
				        return;
					}else{		
						$this->response(
			                array('message' => 'Ukuran file terlalu besar, max 2 MB', 'error' => 400), 
			                REST_Controller::HTTP_BAD_REQUEST
			            );
			            return;
					}
 				}else{
 					$this->response(
		                array('message' => 'File sudah ada', 'error' => 400), 
		                REST_Controller::HTTP_BAD_REQUEST
		            );
		            return;
 				}
 			}else{
 				$this->response(
	                array('message' => 'Jenis file yang boleh diunggah hanya jpg, jpeg, png', 'error' => 400), 
	                REST_Controller::HTTP_BAD_REQUEST
	            );
	            return;
 			}
 		}
    }
}
?>