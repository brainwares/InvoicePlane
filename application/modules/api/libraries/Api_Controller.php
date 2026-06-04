<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Api_Controller extends Base_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Enforce CORS headers
        $this->output->set_header('Access-Control-Allow-Origin: *');
        $this->output->set_header('Access-Control-Allow-Headers: X-API-KEY, Content-Type, Authorization');
        $this->output->set_header('Access-Control-Allow-Methods: GET, OPTIONS');

        // Handle preflight OPTIONS request
        if ($this->input->method(true) === 'OPTIONS') {
            $this->output->set_status_header(200)->_display();
            exit;
        }

        // Enforce GET method only
        if ($this->input->method(true) !== 'GET') {
            $this->response_error('Method Not Allowed', 405);
        }

        // Authenticate request via API Key
        $this->authenticate_api_request();
    }

    /**
     * Authenticates the incoming request using the X-API-KEY header.
     */
    private function authenticate_api_request(): void
    {
        $configured_key = env('API_KEY');

        // If no API Key is configured in ipconfig.php, block all requests for safety
        if (empty($configured_key)) {
            $this->response_error('API is disabled. Please configure API_KEY in ipconfig.php', 500);
        }

        // Retrieve header (checking common casing)
        $provided_key = $this->input->get_request_header('X-API-KEY', true);
        if (!$provided_key) {
            $provided_key = $this->input->get_request_header('x-api-key', true);
        }

        if (empty($provided_key) || !hash_equals($configured_key, $provided_key)) {
            $this->response_error('Unauthorized', 401);
        }
    }

    /**
     * Sends a standardized JSON response and terminates the script.
     *
     * @param mixed $data
     * @param int $status_code
     */
    protected function response_json($data, int $status_code = 200): void
    {
        $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->_display();
        exit;
    }

    /**
     * Sends a standardized error JSON response.
     *
     * @param string $message
     * @param int $status_code
     */
    protected function response_error(string $message, int $status_code = 400): void
    {
        $this->response_json([
            'success' => false,
            'error'   => $message
        ], $status_code);
    }
}
