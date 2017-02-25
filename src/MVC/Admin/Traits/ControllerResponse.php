<?php
/******************************************************************************
 * Copyright (c) 2017. Kitrix Team                                            *
 * Kitrix is open source project, available under MIT license.                *
 *                                                                            *
 * @author: Konstantin Perov <fe3dback@yandex.ru>                             *
 * Documentation:                                                             *
 * @see https://kitrix-org.github.io/docs                                     *
 *                                                                            *
 *                                                                            *
 ******************************************************************************/

namespace Kitrix\MVC\Admin\Traits;

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
    private final function response($data, $status = STATUS_OK) {

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
    public final function ok($data = null) {
        $this->response($data, STATUS_OK);
    }

    /**
     * Immediately clear buffer and return data
     * encoded wia json to connection wia '404 Not Found' status
     *
     * @param $data
     */
    public final function not_found($data = null) {
        $this->response($data, STATUS_NOT_FOUND);
    }

    /**
     * Immediately clear buffer and return data
     * encoded wia json to connection wia '503 Internal Server Error' status
     *
     * @param string $error
     */
    public final function halt($error = "Internal Server Error") {
        $this->response([
            'error' => true,
            'msg' => $error
        ], STATUS_INTERNAL_ERROR);
    }

    /**
     * Immediately clear buffer and return data
     * encoded wia json to connection wia '301 Moved Permanently' status
     *
     * @param $url
     * @param $data
     */
    public final function redirect($data = null, $url = "/") {
        $this->response($data, STATUS_REDIRECT);
    }

}