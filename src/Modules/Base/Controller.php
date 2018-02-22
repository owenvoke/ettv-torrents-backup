<?php

namespace pxgamer\Ettv_Torrents\Modules\Base;

use pxgamer\Ettv_Torrents\Modules\Torrents;
use pxgamer\Ettv_Torrents\Routing;

class Controller extends Routing\Base
{
    public function index()
    {
        $data = new \stdClass();

        $data->total_torrents = Torrents\Model::total();
        $data->years = range(date('Y'), date('Y', strtotime('-5 Years')));

        $this->smarty->display(
            'index.tpl',
            [
                'data' => $data
            ]
        );
    }

    public function errorNotFound()
    {
        $error = new \Error('Page not found.', 404);

        $this->smarty->display(
            'error.tpl',
            [
                'error' => $error
            ]
        );
    }
}
