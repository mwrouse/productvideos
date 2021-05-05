<section id="product-videos" class="page-product-box">
    <h3 class="page-product-heading">{l s='Product Videos'}</h3>
    <div class="product-videos-block">
        {foreach from=$videos item=video}
            <div class="product-video">
                <h4>{$video.title}</h4>
                {$video.embed}
            </div>
        {/foreach}
    </div>
</section>