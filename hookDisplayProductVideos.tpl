{foreach from=$videos item=video}
    <div class="product-video">
        <h4>{$video.title}</h4>
        {$video.embed}
    </div>
{/foreach}