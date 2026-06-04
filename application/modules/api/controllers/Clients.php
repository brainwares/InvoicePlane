<?php
defined('BASEPATH') || exit('No direct script access allowed');

require_once APPPATH . 'modules/api/libraries/Api_Controller.php';

class Clients extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('clients/mdl_clients');
    }

    /**
     * GET /api/clients
     * Lists clients with optional pagination and active status filter.
     */
    public function index()
    {
        $limit  = (int)$this->input->get('limit', true) ?: 50;
        $offset = (int)$this->input->get('offset', true) ?: 0;
        $status = $this->input->get('status', true) ?: 'active';

        if ($status === 'active') {
            $this->mdl_clients->is_active();
        } elseif ($status === 'inactive') {
            $this->mdl_clients->is_inactive();
        }
        // If 'all', do not apply is_active/is_inactive filters

        $clients = $this->mdl_clients
            ->with_total()
            ->with_total_paid()
            ->with_total_balance()
            ->limit($limit, $offset)
            ->get()
            ->result();

        $this->response_json([
            'success' => true,
            'count'   => count($clients),
            'data'    => $clients
        ]);
    }

    /**
     * GET /api/clients/detail/<id>
     * Retrieves detail profile of a client.
     *
     * @param int|null $id
     */
    public function detail($id = null)
    {
        if (empty($id)) {
            $this->response_error('Client ID is required', 400);
        }

        $client = $this->mdl_clients
            ->with_total()
            ->with_total_paid()
            ->with_total_balance()
            ->get_by_id($id);

        if (!$client) {
            $this->response_error('Client not found', 404);
        }

        $this->response_json([
            'success' => true,
            'data'    => $client
        ]);
    }

    /**
     * GET /api/clients/invoices/<id>
     * Retrieves all invoices for a specific client.
     *
     * @param int|null $id
     */
    public function invoices($id = null)
    {
        if (empty($id)) {
            $this->response_error('Client ID is required', 400);
        }

        // Verify client exists
        $client = $this->mdl_clients->get_by_id($id);
        if (!$client) {
            $this->response_error('Client not found', 404);
        }

        $this->load->model('invoices/mdl_invoices');
        
        $invoices = $this->mdl_invoices
            ->where('ip_invoices.client_id', $id)
            ->get()
            ->result();

        $this->response_json([
            'success' => true,
            'client'  => [
                'client_id'   => $client->client_id,
                'client_name' => $client->client_fullname ?: ($client->client_name . ' ' . $client->client_surname)
            ],
            'count'   => count($invoices),
            'data'    => $invoices
        ]);
    }
}
