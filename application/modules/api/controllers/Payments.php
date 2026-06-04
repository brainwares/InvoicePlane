<?php
defined('BASEPATH') || exit('No direct script access allowed');

require_once APPPATH . 'modules/api/libraries/Api_Controller.php';

class Payments extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('payments/mdl_payments');
    }

    /**
     * GET /api/payments
     * Lists payments with pagination and filters.
     */
    public function index()
    {
        $limit  = (int)$this->input->get('limit', true) ?: 50;
        $offset = (int)$this->input->get('offset', true) ?: 0;
        
        $client_id = $this->input->get('client_id', true);
        if (!empty($client_id)) {
            $this->mdl_payments->by_client((int)$client_id);
        }

        $invoice_id = $this->input->get('invoice_id', true);
        if (!empty($invoice_id)) {
            $this->mdl_payments->where('ip_payments.invoice_id', (int)$invoice_id);
        }

        $payments = $this->mdl_payments->limit($limit, $offset)->get()->result();

        $this->response_json([
            'success' => true,
            'count'   => count($payments),
            'data'    => $payments
        ]);
    }
}
