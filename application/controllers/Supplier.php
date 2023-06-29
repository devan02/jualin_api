<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Supplier extends REST_Controller
{
	public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");
        $this->load->model('supplier_m', 'model');
        // Load Authorization Library or Load in autoload config file
        // $this->load->library('authorization_token');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD, OPTIONS");
    }

    public function index_get()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $id = $this->get('id');
            $page = $this->get('page');
            $limit = $this->get('limit');
            $metadata = $this->get('metadata');
            $keyword = $this->get('keyword');

            if($id){
                $data = $this->model->getSupplierById($id);

                $this->response(
                    array('success' => 200, 'result' => $data),
                    REST_Controller::HTTP_OK
                );
                return;
            }else{
                $page = $page == "" ? 0 : $page;
                $limit = $limit == "" ? 10 : $limit;

                $where = "1 = 1";
                if($keyword != ""){
                    $where = $where." AND NAMA_SUPPLIER LIKE '%$keyword%'";
                }

                $data = $this->model->getAllSupplier($page, $limit, $where);

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
                array(
                    'data' => $auth, 
                    'errorKey' => 'unauthorized', 
                    'error' => 401
                ), 
                REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
    }

    public function detail_get($id)
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            if($id != ""){
                $data = $this->model->getSupplierById($id);
                $this->response(
                    array('result' => $data, 'success' => 200), 
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

    function add_leading_zero($value, $threshold = 3) {
        return sprintf('%0' . $threshold . 's', $value);
    }

    public function kode_get()
    {
        # code... huruf awal sup, nomor aut ex = SUP-A1
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $nama = $this->get('nama');

            if($nama == ""){
                $this->response(
                    array('message' => 'Nama supplier belum diisi', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            $subStrNama = strtoupper(substr($nama, 0,1));
            
            $nomor = $this->nomor_m->get_nomor('SUPPLIER', $subStrNama);
            $kode = $subStrNama.$this->add_leading_zero($nomor);
            $arrayName = array('kode' => $kode, 'nama_supplier' => $nama);

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

    public function index_post()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $id = $this->post('id');
            $nama = $this->post('nama');
            $kode = $this->post('kode');
            $alamat = $this->post('alamat');
            $telepon = $this->post('telepon');
            $email = $this->post('email');
            $createDate = date('Y-m-d H:i:s');
            $subStrNama = strtoupper(substr($nama, 0,1));

            if($nama == "" && $alamat == "" && $telepon == "" && $email == ""){
                $this->response(
                    array('message' => 'Niat ngisi gak :D', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            if ($nama == "") {
                $this->response(
                    array('message' => 'Nama supplier tidak boleh kosong', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            if ($alamat == "") {
                $this->response(
                    array('message' => 'Alamat tidak boleh kosong', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            //kondisi nomor telepon
            if ($telepon == "") {
                $this->response(
                    array('message' => 'Telepon tidak boleh kosong', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            $onlyNumberTelepon = $this->handle_m->only_number($telepon);
            if(!$onlyNumberTelepon){
                $this->response(
                    array('message' => 'Nomor telepon '.$telepon.' harus angka', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            $digitTelepon = $this->handle_m->digitPhoneNumber($telepon);
            if($digitTelepon == 'kurang'){
                $this->response(
                    array('message' => 'Digit nomor telepon '.$telepon.' hanya '.strlen($telepon).' digit, tidak sesuai ketentuan', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }
            if($digitTelepon == 'lebih'){
                $lebih = strlen($telepon) - 13;
                $this->response(
                    array('message' => 'Digit nomor telepon '.$telepon.' kelebihan '.$lebih.' digit, max 13 digit', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }
            //end telepon

            // kondisi per email-an
            if ($email == "") {
                $this->response(
                    array('message' => 'Email tidak boleh kosong', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }
            
            if(!isset($id)){
                $cekEmail = $this->model->checkEmailExist($email);
                if(count($cekEmail) > 0){
                    $this->response(
                        array('message' => 'Email '.$email.' sudah ada', 'error' => 400), 
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }
            }
            
            $listEmail = $this->list_email_m->checkingEmail($email); // validasi email dengan domain
            if(!$listEmail){
                $this->response(
                    array('message' => 'Pastikan penulisan email '.$email.' ini benar', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }
            //end kondisi email


            if($id != ""){ //untuk ubah data supplier
                $value = array(
                    'NAMA_SUPPLIER' => $nama,
                    'ALAMAT' => $alamat,
                    'TELEPON' => $telepon,
                    'EMAIL' => $email,
                    'MODIFY_DATE' => date('Y-m-d H:i:s')
                );
                $update = $this->model->editSupplier($id,$value);
                $dataUbah = $this->model->getSupplierById($id);

                if(empty($dataUbah)){
                    $this->response(array('message' => 'Data tidak ditemukan, ID '.$id.' tidak ada dalam data', 'error' => 404), REST_Controller::HTTP_NOT_FOUND);
                    return;
                }

                if($update){
                    $this->response(
                        array('success' => 200, 'message' => 'Data supplier berhasil diubah', 'result' => $dataUbah),
                        REST_Controller::HTTP_OK
                    );
                    return;
                }else{
                    $this->response(array('message' => 'Gagal mengubah data', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST);
                    return;
                }
            }else{
                $value = array(
                    'KODE_SUPPLIER' => $kode,
                    'NAMA_SUPPLIER' => $nama,
                    'ALAMAT' => $alamat,
                    'TELEPON' => $telepon,
                    'EMAIL' => $email,
                    'STATUS' => 'actived',
                    'CREATE_DATE' => $createDate
                );
                $insert = $this->model->postSuppier($value);

                if(!isset($insert)){
                    $this->response(array('message' => 'Gagal menyimpan data', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST);
                    return;
                }else{
                    $this->nomor_m->check_nomor('SUPPLIER', $subStrNama); // nomor auto
                    $dataSupplier = $this->model->getSupplierAfterPost();

                    $this->response(
                        array('success' => 201, 'message' => 'Data supplier tersimpan', 'result' => $dataSupplier),
                        REST_Controller::HTTP_CREATED
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
                ), 
                REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
    }

    public function status_post()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $id = $this->post('id');
            $status = $this->post('status');

            if($id != ""){
                $arrStatus = array('actived','deactived');
                if(!in_array($status, $arrStatus)){
                    $this->response(
                        array('message' => 'Gagal mengubah status', 'error' => 400), 
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }

                $value = array(
                    'STATUS' => $status,
                    'MODIFY_DATE' => date('Y-m-d H:i:s')
                );

                $del = $this->model->editSupplier($id,$value);

                if($del === FALSE){
                    $this->response(
                        array('message' => 'Gagal menghapus data', 'error' => 400), 
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }else{
                    $this->response(
                        array('message' => 'Data berhasil dihapus', 'success' => 200), 
                        REST_Controller::HTTP_OK
                    );
                    return;
                }
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

}
?>