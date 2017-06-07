<?php

namespace pxgamer\Ettv_Torrents\Modules\Torrents;

use pxgamer\Ettv_Torrents\Config;
use pxgamer\Ettv_Torrents\Server;

class Model
{
    public static function search($query = '', $category = null)
    {
        $query = '%' . $query . '%';

        $db = Server\Database::connect();

        if ($category) {
            $stmt = $db->prepare('SELECT *
                                        FROM torrents
                                        WHERE title LIKE :query
                                        AND category = :category
                                        ORDER BY id DESC');
            $stmt->bindParam(':category', $category, \PDO::PARAM_STR);
        } else {
            $stmt = $db->prepare('SELECT *
                                        FROM torrents
                                        WHERE title LIKE :query
                                        ORDER BY id DESC');
        }
        $stmt->bindParam(':query', $query, \PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    public static function total()
    {
        $stmt = Server\Database::connect()->query('SELECT count(*) AS count FROM torrents');
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_OBJ)->count;
    }

    public static function byId($torrent_id)
    {
        if (!is_numeric($torrent_id)) {
            return null;
        }

        $torrent_id = (int)$torrent_id;

        $db = Server\Database::connect();

        $stmt = $db->prepare('SELECT * FROM torrents WHERE id =  :torrent_id');
        $stmt->bindParam(':torrent_id', $torrent_id, \PDO::PARAM_INT);
        $stmt->execute();

        $torrent = $stmt->fetch(\PDO::FETCH_OBJ);

        if (!$torrent) {
            return null;
        }

        if (!isset($torrent->title) || $torrent->title == '') {
            return null;
        }
        foreach ($torrent as $item => $value) {
            if (is_numeric($value)) {
                $torrent->$item = $value;
            }
        }

        $stmt = $db->prepare('SELECT td.tmdb_data, td.id AS meta_id
                                            FROM `meta_data`.tmdb_data td
                                            JOIN `ettv-torrents`.data_link dl ON dl.meta_id = td.id
                                            WHERE dl.torrent_id = :torrent_id');
        $stmt->bindParam(':torrent_id', $torrent_id, \PDO::PARAM_INT);
        $stmt->execute();
        $tmdb_data = $stmt->fetch(\PDO::FETCH_OBJ);

        if (isset($tmdb_data->tmdb_data)) {
            $torrent->tmdb = json_decode($tmdb_data->tmdb_data);
        }

        preg_match('/(HDTV)|([0-9]{3,4}p)/i', $torrent->title, $matches);

        $torrent->quality = $matches[2] ?? $matches[1] ?? 'UNKNOWN';

        if (!isset($torrent->tmdb) || !$torrent->tmdb) {
            $torrent->tmdb = self::addTMDb($torrent);

            if (!$torrent->tmdb) {
                return null;
            }
        }

        if (!$tmdb_data) {
            $stmt = $db->prepare('SELECT td.id AS meta_id
                                            FROM `meta_data`.tmdb_data td
                                            JOIN `ettv-torrents`.data_link dl ON dl.meta_id = td.id
                                            WHERE dl.torrent_id = :torrent_id');
            $stmt->bindParam(':torrent_id', $torrent_id, \PDO::PARAM_INT);
            $stmt->execute();
            $tmdb_data = $stmt->fetch(\PDO::FETCH_OBJ);
        }

        $stmt = $db->prepare('SELECT *
                                FROM torrents t
                                JOIN data_link dl ON dl.torrent_id = t.id
                                WHERE dl.meta_id = :meta_id
                                AND t.id != :torrent_id
                                ORDER BY t.id DESC');
        $stmt->execute([':meta_id' => $tmdb_data->meta_id, 'torrent_id' => $torrent->id]);
        $torrent->related = $stmt->fetchAll(\PDO::FETCH_OBJ);

        return $torrent;
    }

    public static function addTMDb($torrent)
    {
        if (!is_object($torrent)) {
            return null;
        }

        $db = Server\Database::connect();

        $tmdb_id = $torrent->tmdb->tmdb_id ?? null;

        if (!$tmdb_id) {
            preg_match('/^(.*) (\d{4}) (S\d+?E\d+?) .*?\[ettv\]|(.*) (S\d+?E\d+?) .*?\[ettv\]|(.*) (\d{4}) .*?\[ettv\]$/i',
                $torrent->title, $matches);

            if (empty($matches)) {
                return null;
            }

            $title = ($matches[1] != '' ? $matches[1] : ($matches[4] != '' ? $matches[4] : $matches[6]));
            $year = ($matches[2] != '' ? $matches[2] : ($matches[7] != '' ? $matches[7] : ''));

            $url = 'https://api.themoviedb.org/3/search/tv?query=' . urlencode($title) .
                '&primary_release_year=' . urlencode($year) .
                '&language=en-US&page=1&include_adult=false' .
                '&api_key=' . Config\App::TMDB_API_KEY;

            $response = self::curl_it($url);

            if ($response->success) {
                $json = json_decode($response->response);

                if (!isset($json->results[0])) {
                    return null;
                }

                $tmdb_id = $json->results[0]->id;
            }
        }

        $url = 'https://api.themoviedb.org/3/tv/' . $tmdb_id . '?language=en-US&api_key=' . Config\App::TMDB_API_KEY;

        $response = self::curl_it($url);

        if ($response->success) {
            $stmt = $db->prepare('INSERT IGNORE INTO `meta_data`.tmdb_data
                                            (type, tmdb_id, tmdb_data) VALUES
                                            (\'tv\', :tmdb_id, :tmdb_data)');
            $stmt->bindParam(':tmdb_id', $tmdb_id, \PDO::PARAM_INT);
            $stmt->bindParam(':tmdb_data', $response->response, \PDO::PARAM_STR);
            $stmt->execute();
            $meta_id = $db->lastInsertId();

            if (!$meta_id) {
                $stmt = $db->prepare('SELECT id FROM `meta_data`.tmdb_data WHERE type = \'tv\' AND tmdb_id = :tmdb_id');
                $stmt->execute(['tmdb_id' => $tmdb_id]);
                $meta_id = $stmt->fetch()['id'];
            }

            $stmt = $db->prepare('INSERT IGNORE INTO data_link (meta_id, torrent_id) VALUES (:meta_id, :torrent_id)');
            $stmt->bindParam(':meta_id', $meta_id, \PDO::PARAM_INT);
            $stmt->bindParam(':torrent_id', $torrent->id, \PDO::PARAM_INT);
            $stmt->execute();

            return json_decode($response->response);
        }

        return null;
    }

    public static function curl_it($url)
    {
        $status = new \stdClass();
        $status->success = false;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "{}",
        ));

        $status->response = curl_exec($curl);
        $status->error = curl_error($curl);

        curl_close($curl);

        $status->success = !$status->error;
        return $status;
    }

    public static function cron()
    {
        $db = Server\Database::connect();
        $stmt = $db->prepare('INSERT IGNORE INTO torrents (title, info_hash, added, size, category, wwt_link)
                                                              VALUES(:title, :info_hash, :added, :size, :category, :link)');
        header("Content-Type: text/json, text/plain");
        $json_data = file_get_contents(Config\App::CRON_USER);

        $data = json_decode($json_data);
        $added = $failed = 0;
        $new = [];

        if (!isset($data->items) || !$data->items) {
            die();
        }

        foreach ($data->items as $item) {
            $item->name = str_replace('.', ' ', $item->name);
            $stmt->bindParam(':title', $item->name, \PDO::PARAM_STR);
            $stmt->bindParam(':info_hash', $item->info_hash, \PDO::PARAM_STR);
            $stmt->bindParam(':added', $item->added, \PDO::PARAM_STR);
            $stmt->bindParam(':size', $item->size, \PDO::PARAM_INT);
            $stmt->bindParam(':category', $item->cat_parent, \PDO::PARAM_STR);
            $stmt->bindParam(':link', $item->id, \PDO::PARAM_STR);

            if ($stmt->execute()) {
                if ($db->lastInsertId() != 0) {
                    $added = $added + 1;
                    $new[] = str_replace('.', ' ', $item->name);
                }
            } else {
                $failed = $failed + 1;
            }
        }

        if (Config\App::ENV_MODE === Config\App::ENV_DEVELOPMENT) {
            Server\Logger::log(
                (object)[
                    "added" => $added,
                    "new" => $new,
                    "failed" => $failed
                ]
            );
        }

        echo json_encode(
            (object)[
                "added" => $added,
                "new" => $new,
                "failed" => $failed
            ]
        );
    }
}