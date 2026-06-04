<?php
defined('BASEPATH') || exit('No direct script access allowed');

require_once APPPATH . 'modules/api/libraries/Api_Controller.php';

class Stats extends Api_Controller
{
    public function index()
    {
        $this->load->model('invoices/mdl_invoice_amounts');
        $this->load->model('invoices/mdl_invoices');

        $invoice_overview_period = get_setting('invoice_overview_period') ?: 'this-month';

        $status_totals_raw = $this->mdl_invoice_amounts->get_status_totals($invoice_overview_period);
        $status_totals = [];
        
        if (is_array($status_totals_raw)) {
            foreach ($status_totals_raw as $status_id => $data) {
                $status_totals[] = [
                    'status_id'  => (int)$status_id,
                    'label'      => isset($data['label']) ? $data['label'] : '',
                    'sum_total'  => isset($data['sum_total']) ? (float)$data['sum_total'] : 0.0,
                    'num_total'  => isset($data['num_total']) ? (int)$data['num_total'] : 0
                ];
            }
        }

        $stats = [
            'overview_period' => $invoice_overview_period,
            'amounts' => [
                'invoiced' => [
                    'month' => (float)$this->mdl_invoice_amounts->get_total_invoiced('month'),
                    'year'  => (float)$this->mdl_invoice_amounts->get_total_invoiced('year'),
                    'total' => (float)$this->mdl_invoice_amounts->get_total_invoiced()
                ],
                'paid' => [
                    'month' => (float)$this->mdl_invoice_amounts->get_total_paid('month'),
                    'year'  => (float)$this->mdl_invoice_amounts->get_total_paid('year'),
                    'total' => (float)$this->mdl_invoice_amounts->get_total_paid()
                ],
                'balance' => [
                    'month' => (float)$this->mdl_invoice_amounts->get_total_balance('month'),
                    'year'  => (float)$this->mdl_invoice_amounts->get_total_balance('year'),
                    'total' => (float)$this->mdl_invoice_amounts->get_total_balance()
                ]
            ],
            'invoice_status_breakdown' => $status_totals
        ];

        $this->response_json([
            'success' => true,
            'data'    => $stats
        ]);
    }
}
