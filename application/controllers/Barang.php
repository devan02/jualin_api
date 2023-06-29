<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Barang extends REST_Controller
{
	public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");
        $this->load->model('barang_m', 'model');
        $this->load->model('supplier_m', 'sup_m');
        $this->load->model('kategori_barang_m', 'kat_m');
        $this->load->model('departemen_barang_m', 'dep_m');
        // Load Authorization Library or Load in autoload config file
        // $this->load->library('authorization_token');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD, OPTIONS");
    }

    function add_leading_zero($value, $threshold = 6) {
        return sprintf('%0' . $threshold . 's', $value);
    }

    function plu_get()
    {
        # code... 999999
        $nomor = $this->nomor_m->get_nomor('PLU');
        $kode = $this->add_leading_zero($nomor);
        $arrayName = array('plu' => $kode);

        $this->response(
            array('success' => 200, 'result' => $arrayName),
            REST_Controller::HTTP_OK
        );
        return;
    }

    function index_get(){
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
            $id = $this->get('id');
            $page = $this->get('page');
            $limit = $this->get('limit');
            $metadata = $this->get('metadata');
            $keyword = $this->get('keyword');
    		$status = $this->get('status');

            if($id){
                if($id == 0 || $id == '0'){
                    $this->response(
                        array(
                            'message' => 'Inputan tidak boleh 0',
                            'errorKey' => 'validationNumber',
                            'error' => 403
                        ), REST_Controller::HTTP_METHOD_NOT_ALLOWED
                    );
                    return;
                }

                if(!preg_replace("/[^0-9]/", "", $id)){
                    $this->response(
                        array(
                            'message' => 'Inputan harus angka / number',
                            'errorKey' => 'mustNumberActivation',
                            'error' => 403
                        ), REST_Controller::HTTP_METHOD_NOT_ALLOWED
                    );
                    return;
                }

                $data = $this->model->getDetailBarang($id);

                $this->response(
                    array('success' => 200, 'result' => $data),
                    REST_Controller::HTTP_OK
                );
                return;
            }else{
                $data = $this->model->getAllBarang($page, $limit, $keyword, $status);
                
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

    function index_post(){
    	$auth = $this->authorization_token->validateToken();

    	if($auth['status']){
    		$plu = $this->post('plu');
            $barcode = $this->post('barcode');
			$nama = $this->post('nama');
            $harga_beli = 0;
            $harga_jual = str_replace(',', '', $this->post('harga_jual'));
            $id_departemen = $this->post('id_departemen');
            $departemen = "";
            $id_kategori = $this->post('id_kategori');
            $kategori = "";
            $id_supplier = $this->post('id_supplier');
            $supplier = "";
            $satuan = $this->post('satuan');
            $status = 'actived';
			$createDate = date('Y-m-d H:i:s');

            if($plu == "" || $plu == null){
                $this->response(
                    array('message' => 'PLU tidak boleh kosong', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            if($barcode == "" || $barcode == null){
                $this->response(
                    array('message' => 'Barcode tidak boleh kosong', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

			if($nama == "" || $nama == null){
				$this->response(
                    array('message' => 'Nama barang tidak boleh kosong', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
                );
                return;
			}

            if($harga_jual == "" || $harga_jual == null){
                $this->response(
                    array('message' => 'Harga jual tidak boleh kosong', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            if($id_departemen == "" || $id_departemen == null){
                $this->response(
                    array('message' => 'Departemen barang tidak boleh kosong', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }else{
                $data = $this->dep_m->getDepartemenById($id_departemen);
                $departemen = $data['NAMA'];
            }

            if($id_kategori == "" || $id_kategori == null){
                $this->response(
                    array('message' => 'Kategori barang tidak boleh kosong', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }else{
                $data = $this->kat_m->getKategoriById($id_kategori);
                $kategori = $data['NAMA_KATEGORI'];
            }

            if($id_supplier == "" || $id_supplier == null){
                $this->response(
                    array('message' => 'Supplier barang tidak boleh kosong', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }else{
                $data = $this->sup_m->getSupplierById($id_supplier);
                $supplier = $data['NAMA_SUPPLIER'];
            }

            if($satuan == "" || $satuan == null){
                $this->response(
                    array('message' => 'Satuan barang tidak boleh kosong', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

			$value = array(
				'PLU' => $plu,
                'BARCODE' => $barcode,
                'NAMA_BARANG' => $nama,
                'HARGA_BELI' => $harga_beli,
                'HARGA_JUAL' => $harga_jual,
                'ID_DEPARTEMEN_BARANG' => $id_departemen,
                'DEPARTEMEN_BARANG' => $departemen,
                'ID_KATEGORI' => $id_kategori,
                'KATEGORI' => $kategori,
                'ID_SUPPLIER' => $id_supplier,
                'SUPPLIER' => $supplier,
                'SATUAN' => $satuan,
                'STATUS' => $status,
                'CREATE_DATE' => $createDate
			);
			$insert = $this->model->postBarang($value);

			if(!isset($insert)){
				$this->response(array('message' => 'Gagal menyimpan data', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST);
                return;
			}else{
				$this->nomor_m->check_nomor('PLU'); // nomor auto
                $data = $this->model->getBarangAfterInsert();

                $this->response(
                    array('success' => 201, 'message' => 'Data berhasil disimpan', 'result' => $data),
                    REST_Controller::HTTP_CREATED
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

    function edit_post()
    {
    	$auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $id = $this->post('id');
            $barcode = $this->post('barcode');
            $nama = $this->post('nama');
            $harga_beli = 0;
            $harga_jual = str_replace(',', '', $this->post('harga_jual'));
            $id_departemen = $this->post('id_departemen');
            $departemen = "";
            $id_kategori = $this->post('id_kategori');
            $kategori = "";
            $id_supplier = $this->post('id_supplier');
            $supplier = "";
            $satuan = $this->post('satuan');
            $createDate = date('Y-m-d H:i:s');

            if($barcode == "" || $barcode == null){
                $this->response(
                    array('message' => 'Barcode tidak boleh kosong', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            if($nama == "" || $nama == null){
                $this->response(
                    array('message' => 'Nama barang tidak boleh kosong', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            if($harga_jual == "" || $harga_jual == null){
                $this->response(
                    array('message' => 'Harga jual tidak boleh kosong', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            if($id_departemen == "" || $id_departemen == null){
                $this->response(
                    array('message' => 'Departemen barang tidak boleh kosong', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }else{
                $data = $this->dep_m->getDepartemenById($id_departemen);
                $departemen = $data['NAMA'];
            }

            if($id_kategori == "" || $id_kategori == null){
                $this->response(
                    array('message' => 'Kategori barang tidak boleh kosong', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }else{
                $data = $this->kat_m->getKategoriById($id_kategori);
                $kategori = $data['NAMA_KATEGORI'];
            }

            if($id_supplier == "" || $id_supplier == null){
                $this->response(
                    array('message' => 'Supplier barang tidak boleh kosong', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }else{
                $data = $this->sup_m->getSupplierById($id_supplier);
                $supplier = $data['NAMA_SUPPLIER'];
            }

            if($satuan == "" || $satuan == null){
                $this->response(
                    array('message' => 'Satuan barang tidak boleh kosong', 'error' => 400), REST_Controller::HTTP_BAD_REQUEST
                );
                return;
            }

            $value = array(
                'BARCODE' => $barcode,
                'NAMA_BARANG' => $nama,
                'HARGA_BELI' => $harga_beli,
                'HARGA_JUAL' => $harga_jual,
                'ID_DEPARTEMEN_BARANG' => $id_departemen,
                'DEPARTEMEN_BARANG' => $departemen,
                'ID_KATEGORI' => $id_kategori,
                'KATEGORI' => $kategori,
                'ID_SUPPLIER' => $id_supplier,
                'SUPPLIER' => $supplier,
                'SATUAN' => $satuan,
                'MODIFY_DATE' => $createDate
            );

            $this->db->where('ID', $id);
            $this->db->update('adm_barang', $value);

            $data = $this->model->getDetailBarang($id);

            $this->response(
                array('success' => 201, 'message' => 'Data berhasil diperbarui', 'result' => $data),
                REST_Controller::HTTP_CREATED
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

    function status_post()
    {
        $auth = $this->authorization_token->validateToken();

        if($auth['status']){
            $id = $this->post('id');
            $status = $this->post('status');

            if($id != ""){
                $sql = "UPDATE adm_barang SET STATUS = '$status' WHERE ID = '$id'";
                $del = $this->db->query($sql);

                if($del === FALSE){
                    $this->response(
                        array('message' => 'Gagal memperbarui status barang', 'error' => 400), 
                        REST_Controller::HTTP_BAD_REQUEST
                    );
                    return;
                }else{
                    $this->response(
                        array('message' => 'Status barang berhasil diperbarui', 'success' => 200), 
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