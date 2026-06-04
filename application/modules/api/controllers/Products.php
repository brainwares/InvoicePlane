<?php
defined('BASEPATH') || exit('No direct script access allowed');

require_once APPPATH . 'modules/api/libraries/Api_Controller.php';

class Products extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('products/mdl_products');
    }

    /**
     * GET /api/products
     * Lists products with pagination and optional search query or family filter.
     */
    public function index()
    {
        $limit  = (int)$this->input->get('limit', true) ?: 50;
        $offset = (int)$this->input->get('offset', true) ?: 0;
        
        $search = $this->input->get('search', true);
        if (!empty($search)) {
            $this->mdl_products->by_product($search);
        }

        $family_id = $this->input->get('family_id', true);
        if (!empty($family_id)) {
            $this->mdl_products->by_family((int)$family_id);
        }

        $products = $this->mdl_products->limit($limit, $offset)->get()->result();

        $this->response_json([
            'success' => true,
            'count'   => count($products),
            'data'    => $products
        ]);
    }

    /**
     * GET /api/products/detail/<id>
     * Retrieves detail profile of a product.
     *
     * @param int|null $id
     */
    public function detail($id = null)
    {
        if (empty($id)) {
            $this->response_error('Product ID is required', 400);
        }

        $product = $this->mdl_products->get_by_id($id);

        if (!$product) {
            $this->response_error('Product not found', 404);
        }

        $this->response_json([
            'success' => true,
            'data'    => $product
        ]);
    }
}
