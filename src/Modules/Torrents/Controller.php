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

    public function show()
    {
        $data = new \stdClass();

        $torrent_id = $this->request->args['id'] ?? null;

        $data->torrent = Torrents\Model::byId($torrent_id);

        if ($data->torrent) {
            $this->smarty->display(
                'torrents/show.tpl',
                [
                    'data' => $data
                ]
            );
        } else {
            $error = new \Error('Torrent not found.', 404);
            http_response_code(404);

            $this->smarty->display(
                'error.tpl',
                [
                    'error' => $error
                ]
            );
        }
    }

    public function cron()
    {
        Torrents\Model::cron();
    }
}