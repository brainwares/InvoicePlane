<?php
defined('BASEPATH') || exit('No direct script access allowed');

require_once APPPATH . 'modules/api/libraries/Api_Controller.php';

class Quotes extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('quotes/mdl_quotes');
    }

    /**
     * GET /api/quotes
     * Lists quotes with pagination and filters.
     */
    public function index()
    {
        $limit  = (int)$this->input->get('limit', true) ?: 50;
        $offset = (int)$this->input->get('offset', true) ?: 0;
        
        $client_id = $this->input->get('client_id', true);
        if (!empty($client_id)) {
            $this->mdl_quotes->by_client((int)$client_id);
        }

        $status = $this->input->get('status', true);
        if (!empty($status)) {
            if ($status === 'draft') {
                $this->mdl_quotes->is_draft();
            } elseif ($status === 'sent') {
                $this->mdl_quotes->is_sent();
            } elseif ($status === 'viewed') {
                $this->mdl_quotes->is_viewed();
            } elseif ($status === 'approved') {
                $this->mdl_quotes->is_approved();
            } elseif ($status === 'rejected') {
                $this->mdl_quotes->is_rejected();
            } elseif ($status === 'canceled') {
                $this->mdl_quotes->is_canceled();
            } elseif (is_numeric($status)) {
                $this->mdl_quotes->where('ip_quotes.quote_status_id', (int)$status);
            }
        }

        $quotes = $this->mdl_quotes->limit($limit, $offset)->get()->result();

        $this->response_json([
            'success' => true,
            'count'   => count($quotes),
            'data'    => $quotes
        ]);
    }

    /**
     * GET /api/quotes/detail/<id>
     * Retrieves full detail of a specific quote.
     *
     * @param int|null $id
     */
    public function detail($id = null)
    {
        if (empty($id)) {
            $this->response_error('Quote ID is required', 400);
        }

        $quote = $this->mdl_quotes->get_by_id($id);

        if (!$quote) {
            $this->response_error('Quote not found', 404);
        }

        // Fetch quote items
        $this->load->model('quotes/mdl_quote_items');
        $items = $this->mdl_quote_items->where('quote_id', $id)->get()->result();
        $quote->items = $items;

        $this->response_json([
            'success' => true,
            'data'    => $quote
        ]);
    }
}
