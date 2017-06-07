{include file='include/header.tpl'}
<div class="container text-center">
    <div class="row">
        <div class="col-md-4">
            <img class="poster-image" alt="Poster" src="//image.tmdb.org/t/p/w185{$data->torrent->tmdb->poster_path}">
        </div>
        <div class="col-md-8 text-left">
            <h2 class="page-header">
                <span>{$data->torrent->tmdb->name}</span>
                <span>({$data->torrent->tmdb->first_air_date|absolute_time:'Y'})</span>
            </h2>
            <span class="small">
                <span title="Type">
                    <span class="fa fa-fw fa-folder-open-o" aria-hidden="true"></span>
                    <span>{$data->torrent->tmdb->type}</span>
                </span>
                <span title="Quality">
                    <span class="fa fa-fw fa-video-camera" aria-hidden="true"></span>
                    <span>{$data->torrent->quality}</span>
                </span>
                <span title="Download via Magnet">
                    {pxgamer\Ettv_Torrents\Modules\Torrents\Helper::magnetLink($data->torrent->info_hash, $data->torrent->title, true)}
                </span>
                <span title="Download via Torrent">
                    {pxgamer\Ettv_Torrents\Modules\Torrents\Helper::torrentLink($data->torrent->info_hash, $data->torrent->title, true)}
                </span>
                {if $data->torrent->tmdb->original_name !== $data->torrent->tmdb->name}
                    <span title="Original Title">
                        <span class="fa fa-fw fa-language" aria-hidden="true"></span>
                        <span>{$data->torrent->tmdb->original_name}</span>
                    </span>
                {/if}
            </span>
            <hr>
            <div class="summary">
                <p>{$data->torrent->tmdb->overview}</p>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h3>Rating</h3>
                    <p>Rated an average of {$data->torrent->tmdb->vote_average} by {$data->torrent->tmdb->vote_count}
                        people.</p>
                </div>
                <div class="col-md-6">
                    <h3>Languages</h3>
                    <ul class="list-unstyled">
                        {foreach $data->torrent->tmdb->languages as $language}
                            <li>{$language}</li>
                        {/foreach}
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h3>Genres</h3>
                    <ul class="list-unstyled">
                        {foreach $data->torrent->tmdb->genres as $genre}
                            <li>{$genre->name}</li>
                        {/foreach}
                    </ul>
                </div>
                <div class="col-md-6">
                    <h3>More Info</h3>
                    <ul class="list-unstyled">
                        <li>
                            <strong>Seasons:</strong>
                            <span>{$data->torrent->tmdb->number_of_seasons}</span>
                        </li>
                        <li>
                            <strong>Episodes:</strong>
                            <span>{$data->torrent->tmdb->number_of_episodes}</span>
                        </li>
                        <li>
                            <strong>First Aired:</strong>
                            <span>{$data->torrent->tmdb->first_air_date}</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <h3>Other Sites</h3>
                    <ul class="list-inline">
                        <li>
                            <a class="no-underline" target="_blank"
                               title="TMDb"
                               href="//www.themoviedb.org/tv/{$data->torrent->tmdb->id}">
                                <i class="fa fa-comment" aria-hidden="true"></i> TMDb
                            </a>
                        </li>
                        {if $data->torrent->tmdb->homepage}
                            <li>
                                <a class="no-underline" target="_blank"
                                   title="Homepage"
                                   href="{$data->torrent->tmdb->homepage}">
                                    <i class="fa fa-link" aria-hidden="true"></i> Homepage
                                </a>
                            </li>
                        {/if}
                        {if $data->torrent->wwt_link}
                            <li>
                                <a class="no-underline" target="_blank"
                                   title="Download from WorldWide Torrents"
                                   href="//worldwidetorrents.eu/torrents-details.php?id={$data->torrent->wwt_link}">
                                    <i class="fa fa-globe" aria-hidden="true"></i> WorldWide Torrents
                                </a>
                            </li>
                        {/if}
                        {if $data->torrent->tpb_link}
                            <li>
                                <a class="no-underline" target="_blank"
                                   title="Download from WorldWide Torrents"
                                   href="//thepiratebay.org/torrent/{$data->torrent->tpb_link}">
                                    <i class="fa fa-ship" aria-hidden="true"></i> The Pirate Bay
                                </a>
                            </li>
                        {/if}
                    </ul>
                </div>
            </div>
            <h3>Related Torrents</h3>
            <table class="table table-striped">
                {foreach $data->torrent->related as $related}
                    <tr>
                        <td>
                            <a class="no-underline" href="/torrent/{$related->torrent_id}">
                                <span>{$related->title}</span>
                            </a>
                        </td>
                        <td class="date-column" style="width: 180px">
                            <span>{$related->added|absolute_time}</span>
                        </td>
                    </tr>
                {/foreach}
            </table>
        </div>
    </div>
</div>
{include file='include/footer.tpl'}
