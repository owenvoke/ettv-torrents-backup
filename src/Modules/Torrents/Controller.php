<?php

namespace pxgamer\Ettv_Torrents\Modules\Torrents;

use pxgamer\Ettv_Torrents\Modules\Torrents;
use pxgamer\Ettv_Torrents\Routing;

class Controller extends Routing\Base
{
    public function search()
    {
        $data = new \stdClass();

        $query = $this->request->query['q'] ?? null;
        $category = $this->request->query['c'] ?? null;

        $data->torrents = Torrents\Model::search($query, $category);

        $this->smarty->display(
            'torrents/search.tpl',
            [
                'data' => $data
            ]
        );
    }

    public function cron()
    {
        Torrents\Model::cron();
    }
}