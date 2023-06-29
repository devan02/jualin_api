<?php use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Kategori_barang extends REST_Controller
{
	public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");
        $this->load->model('kategori_barang_m', 'model');
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

            $page = $page == "" ? 0 : $page;
            $limit = $limit == "" ? 10 : $limit;

            $where = "1 = 1";
            if($keyword != ""){
                $where = $where." AND NAMA LIKE '%$keyword%'";
            }

            if($id){
                $data = $this->model->getKategoriById($id);

                $this->response(
                    array('success' => 200, 'result' => $data),
                    REST_Controller::HTTP_OK
                );
                return;
            }else{
                $data = $this->model->getKategori($page, $limit, $where);
                
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

    function add_leading_zero($value, $threshold = 3) {
        return sprintf('%0' . $threshold . 's', $value);
    }

    public function kode_get()
    {
        # code...
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $nama = $this->get('nama');            
            $nomor = $this->nomor_m->get_nomor('KATEGORI');
            $kode = 'K'.$this->add_leading_zero($nomor);

            $this->response(
                array('success' => 200, 'kode' => $kode, 'kategori' => $nama),
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
        # code...
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $kode = $this->post('kode');          
            $nama = $this->post('nama');

            if ($nama == "") {
                $this->response(
                    array('message' => 'Nama kategori tidak boleh kosong', 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            $dataByKode = $this->model->getKategoriByCode($kode);
            if(!empty($dataByKode)){
                $kategori = $dataByKode['NAMA'];
                $this->response(
                    array('message' => 'Kode '.$kode.' telah digunakan kategori '.$kategori, 'error' => 400), 
                    REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            $value = array(
                'KODE' => $kode, 
                'NAMA' => $nama, 
                'STATUS' => 'actived', 
                'CREATE_DATE' => date('Y-m-d H:i:s') 
            );

            $this->model->postKategori($value);
            $this->nomor_m->check_nomor('KATEGORI');

            $this->response(
                array('success' => 200, 'message' => 'Data berhasil disimpan', 'result' => $this->model->getKategoriAfterInsert()),
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

    public function edit_post()
    {
        # code...
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $id = $this->post('id');          

            if(!empty($id)){
                $nama = $this->post('nama');

                if(empty($this->model->getKategoriById($id))){
                    $this->response(
                        array('message' => 'Data belum tersedia saat ini', 'error' => 400), 
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }
                
                if ($nama == "") {
                    $this->response(
                        array('message' => 'Nama kategori tidak boleh kosong', 'error' => 400), 
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }

                $value = array(                 
                    'NAMA' => $nama,
                    'MODIFY_DATE' => date('Y-m-d H:i:s') 
                );

                $this->model->editKategori($id,$value);

                $this->response(
                    array('success' => 200, 'message' => 'Data berhasil diubah', 'result' => $this->model->getKategoriById($id)),
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

    public function change_status_post()
    {
        # code...
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $id = $this->post('id');          

            if(!empty($id)){
                $status = $this->post('status');

                if(empty($this->model->getKategoriById($id))){
                    $this->response(
                        array('message' => 'Data belum tersedia saat ini', 'error' => 400), 
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }
                
                if ($status == "") {
                    $this->response(
                        array('message' => 'Status kategori tidak boleh kosong', 'error' => 400), 
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }

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

                $this->model->editKategori($id,$value);

                $this->response(
                    array('success' => 200, 'message' => 'Status kategori berhasil diubah', 'result' => $this->model->getKategoriById($id)),
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

}
?>