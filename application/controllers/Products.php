<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends MY_Controller
{
    protected $allowed_roles = array('ADMIN');

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Product_model', 'Category_model'));
    }

    public function index()
    {
        $data = array(
            'page_title'   => 'Sản phẩm',
            'current_user' => $this->current_user,
            'products'     => $this->Product_model->get_all(),
        );
        $this->load->view('layout/header', $data);
        $this->load->view('products/index', $data);
        $this->load->view('layout/footer');
    }

    public function create()
    {
        $error = NULL;

        if ($this->input->method() === 'post')
        {
            $image = $this->_handle_image_upload($error);

            if ( ! $error)
            {
                $id = $this->Product_model->create($this->_form_data($image));
                $this->audit('product', 'CREATE', NULL, array('id' => $id));
                redirect('products');
                return;
            }
        }

        $data = array(
            'page_title'   => 'Thêm sản phẩm',
            'current_user' => $this->current_user,
            'product'      => NULL,
            'categories'   => $this->Category_model->get_active(),
            'error'        => $error,
        );
        $this->load->view('layout/header', $data);
        $this->load->view('products/form', $data);
        $this->load->view('layout/footer');
    }

    public function edit($id)
    {
        $product = $this->Product_model->get_by_id($id);
        if ( ! $product) show_404();
        $error = NULL;

        if ($this->input->method() === 'post')
        {
            $image = $this->_handle_image_upload($error, $product['image']);

            if ( ! $error)
            {
                $this->Product_model->update($id, $this->_form_data($image));
                $this->audit('product', 'UPDATE', $product, array('id' => $id));
                redirect('products');
                return;
            }
        }

        $data = array(
            'page_title'   => 'Sửa sản phẩm',
            'current_user' => $this->current_user,
            'product'      => $product,
            'categories'   => $this->Category_model->get_active(),
            'error'        => $error,
        );
        $this->load->view('layout/header', $data);
        $this->load->view('products/form', $data);
        $this->load->view('layout/footer');
    }

    public function delete($id)
    {
        $product = $this->Product_model->get_by_id($id);
        $this->Product_model->delete($id);
        $this->audit('product', 'DELETE', $product, NULL);
        redirect('products');
    }

    private function _form_data($image)
    {
        return array(
            'category_id'  => (int) $this->input->post('category_id'),
            'sku'          => $this->input->post('sku', TRUE),
            'product_name' => $this->input->post('product_name', TRUE),
            'price'        => (float) $this->input->post('price'),
            'description'  => $this->input->post('description', TRUE),
            'status'       => $this->input->post('status') ?: 'ACTIVE',
            'image'        => $image,
        );
    }

    /**
     * Handles the optional product image upload. Returns the relative path to
     * store in products.image, or the existing path unchanged when no new
     * file was chosen. Sets $error (by reference) and returns NULL on failure.
     */
    private function _handle_image_upload(&$error, $existing_image = NULL)
    {
        if (empty($_FILES['image']['name']))
        {
            return $existing_image;
        }

        $upload_dir = FCPATH.'assets/uploads/products/';
        if ( ! is_dir($upload_dir))
        {
            mkdir($upload_dir, 0755, TRUE);
        }

        $this->load->library('upload', array(
            'upload_path'   => $upload_dir,
            'allowed_types' => 'jpg|jpeg|png|webp',
            'max_size'      => 2048,
            'encrypt_name'  => TRUE,
        ));

        if ( ! $this->upload->do_upload('image'))
        {
            $error = $this->upload->display_errors('', '');
            return NULL;
        }

        if ($existing_image && is_file(FCPATH.'assets/'.$existing_image))
        {
            @unlink(FCPATH.'assets/'.$existing_image);
        }

        return 'uploads/products/'.$this->upload->data('file_name');
    }
}
