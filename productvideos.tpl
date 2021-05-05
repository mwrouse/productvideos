<div class="product-video-containers">
    {foreach from=$videos item=video}
        <div class="product-video">
            <h2>{$video.title}</h2>
            {$video.embed}
        </div>
    {/foreach}
</div>