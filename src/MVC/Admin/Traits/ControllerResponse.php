<?php namespace Kitrix\MVC\Admin\Traits;

const STATUS_OK = 200;
const STATUS_REDIRECT = 301;
const STATUS_NOT_FOUND = 404;
const STATUS_INTERNAL_ERROR = 503;

trait ControllerResponse
{

    /**
     * Return json response to request connection
     *
     * @param $data
     * @param int $status
     */
    private function response($data, $status = STATUS_OK) {

        ob_end_clean();
        http_response_code((int)$status);
        $data = json_encode($data, JSON_PRETTY_PRINT);
        die($data);
    }

    /**
     * Immediately clear buffer and return data
     * encoded wia json to connection wia '200 OK' status
     *
     * @param $data
     */
    public function ok($data = null) {
        $this->response($data, STATUS_OK);
    }

    /**
     * Immediately clear buffer and return data
     * encoded wia json to connection wia '404 Not Found' status
     *
     * @param $data
     */
    public function not_found($data = null) {
        $this->response($data, STATUS_NOT_FOUND);
    }

    /**
     * Immediately clear buffer and return data
     * encoded wia json to connection wia '503 Internal Server Error' status
     *
     * @param string $error
     */
    public function halt($error = "Internal Server Error") {
        $this->response([
            'error' => true,
            'explain' => $error
        ], STATUS_INTERNAL_ERROR);
    }

    /**
     * Immediately clear buffer and return data
     * encoded wia json to connection wia '301 Moved Permanently' status
     *
     * @param $url
     * @param $data
     */
    public function redirect($data = null, $url = "/") {
        $this->response($data, STATUS_REDIRECT);
    }

}