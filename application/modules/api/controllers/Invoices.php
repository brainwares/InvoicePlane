<?php
defined('BASEPATH') || exit('No direct script access allowed');

require_once APPPATH . 'modules/api/libraries/Api_Controller.php';

class Invoices extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('invoices/mdl_invoices');
    }

    /**
     * GET /api/invoices
     * Lists invoices with client and status filters.
     */
    public function index()
    {
        $limit  = (int)$this->input->get('limit', true) ?: 50;
        $offset = (int)$this->input->get('offset', true) ?: 0;
        
        $client_id = $this->input->get('client_id', true);
        if (!empty($client_id)) {
            $this->mdl_invoices->where('ip_invoices.client_id', (int)$client_id);
        }

        $status = $this->input->get('status', true);
        if (!empty($status)) {
            if ($status === 'draft') {
                $this->mdl_invoices->is_draft();
            } elseif ($status === 'sent') {
                $this->mdl_invoices->is_sent();
            } elseif ($status === 'viewed') {
                $this->mdl_invoices->is_viewed();
            } elseif ($status === 'paid') {
                $this->mdl_invoices->is_paid();
            } elseif ($status === 'overdue') {
                $this->mdl_invoices->is_overdue();
            } elseif (is_numeric($status)) {
                $this->mdl_invoices->where('ip_invoices.invoice_status_id', (int)$status);
            }
        }

        $invoices = $this->mdl_invoices->limit($limit, $offset)->get()->result();

        $this->response_json([
            'success' => true,
            'count'   => count($invoices),
            'data'    => $invoices
        ]);
    }

    /**
     * GET /api/invoices/detail/<id>
     * Retrieves full detail of a specific invoice.
     *
     * @param int|null $id
     */
    public function detail($id = null)
    {
        if (empty($id)) {
            $this->response_error('Invoice ID is required', 400);
        }

        $invoice = $this->mdl_invoices->get_by_id($id);

        if (!$invoice) {
            $this->response_error('Invoice not found', 404);
        }

        // Fetch invoice items
        $this->load->model('invoices/mdl_items');
        $items = $this->mdl_items->where('invoice_id', $id)->get()->result();
        $invoice->items = $items;

        $this->response_json([
            'success' => true,
            'data'    => $invoice
        ]);
    }
}
