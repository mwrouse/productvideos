<style>
    #product-videos table {
        width: 100%;
    }
    #product-videos table td {
            padding: 10px;
            padding-left: 0;
    }
    #product-videos table tbody tr:hover {
        background-color: #f7f7f7;
    }

    #product-videos td.delete i:hover {
        cursor: pointer;
        color: #dc3545;
    }

    #add-video-btn:hover {
        cursor: pointer;
        color: #ff6000;
    }


</style>
<div id="product-videos" class="panel product-tab">
    <input type="hidden" name="submittted_tabs[]" value="ProductVideos">
    <h3>{l s='Videos'}</h3>

    <div class="form-group">
		<table>
            <thead>
                <tr>
                    <th><strong>Title</strong></th>
                    <th><strong>URL</strong></th>
                    <th class="text-center"><strong>Include in Images</strong></th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="product-video-form">
                {foreach from=$videos item=video}
                    <tr>
                        <td>
                            <input type="hidden" value="{$video.id}" name="video_ids[]"/>
                            <input type="text" value="{$video.title}" class="form-control" name="{$video.id}_title"/>
                        </td>
                        <td>
                            <input type="text" value="{$video.url}" class="form-control" name="{$video.id}_url"/>
                        </td>
                        <td class="text-center">
                            <input type="checkbox" name="video_image_include[]" {if $video.included}checked{/if} value="{$video.id}"/>
                        </td>
                        <td class="delete">
                            <i class="process-icon-delete" title="{l s='Remove Video'}" onclick="deleteVideo(event)"></i>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
        <div id="add-video-btn" style="width: 100%; text-align: center" onclick="addNewProductVideo()">
            <i class="process-icon-new"></i>
            {l s="Add Video"}
        </div>
	</div>

    <div id="deleted-videos-wrapper">
    </div>

    <div class="panel-footer">
		<a href="{$link->getAdminLink('AdminProducts')|escape:'html':'UTF-8'}{if isset($smarty.request.page) && $smarty.request.page > 1}&amp;submitFilterproduct={$smarty.request.page|intval}{/if}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel'}</a>
		<button type="submit" name="submitAddproduct" class="btn btn-default pull-right" disabled="disabled"><i class="process-icon-loading"></i> {l s='Save'}</button>
		<button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right" disabled="disabled"><i class="process-icon-loading"></i> {l s='Save and Stay'}</button>
	</div>
</div>

<script type="text/javascript">
    function uniqid(prefix = "", random = false) {
        const sec = Date.now() * 1000 + Math.random() * 1000;
        const id = sec.toString(16).replace(/\./g, "").padEnd(14, "0");
        return prefix+id+(random ? Math.trunc(Math.random() * 100000000):"");
    };

    function addNewProductVideo() {
        var container = document.getElementById('product-video-form');
        if (container == null) {
            alert('Add New Video Failed: container not found');
            return;
        }

        var id = uniqid();

        var html = '<td><input type="hidden" value="'+id+'" name="video_ids[]"/><input type="text" value="" class="form-control" name="'+id+'_title"/></td><td><input type="text" value="" class="form-control" name="'+id+'_url"/></td><td class="text-center"><input type="checkbox" name="video_image_include[]" value="'+id+'"/></td><td class="delete"><i class="process-icon-delete" title="{l s="Remove Video"}" onclick="deleteVideo(event)"></i></td>';
        var el = document.createElement('tr');
        el.innerHTML = html;

        container.appendChild(el);
    }

    function deleteVideo(e) {
        var container = document.getElementById('product-video-form');
        if (container == null) {
            alert('Removing Video Failed: container not found');
            return;
        }

        var deletedContainer = document.getElementById('deleted-videos-wrapper');
        if (deletedContainer == null) {
            alert('Removing Video Failed: deletedContainer not found');
            return;
        }

        var row = e.currentTarget.parentNode.parentNode;

        if (confirm("{l s='Are you sure you want to delete this video?'}"))
        {
            var videoId = null;
            var videoIdInput = row.querySelector('input[type="hidden"]');
            if (videoIdInput != null)
                videoId = videoIdInput.value;

            if (videoId != null) {
                var el = document.createElement('input');
                el.setAttribute('type', 'hidden');
                el.setAttribute('value', videoId);
                el.setAttribute('name', 'deleted_videos[]');

                deletedContainer.appendChild(el);
            }

            container.removeChild(row);
        }
    }

</script>