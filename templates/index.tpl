{include file='include/header.tpl'}
<div class="container text-center">
    <div class="page-header">
        <h1 class="ettv-blue">{$_config::APP_NAME}</h1>
    </div>
    <div class="form-group">
        <form action="/search" method="get">
            <input placeholder="I want to download..." autocomplete="off" name="q"
                   type="text" class="hover-bottom big-search">
        </form>
    </div>
    <div class="years form-group ettv-blue">
        <ul>
            {foreach $data->years as $year}
                <li><a href="/search?q={$year}">{$year}</a></li>
            {/foreach}
        </ul>
    </div>
    <div class="text-center">
        <small>{$data->total_torrents} torrents and counting...</small>
    </div>
</div>
{include file='include/footer.tpl'}