<style>
    #product-videos-config table {
        width: 100%;
    }
    #product-videos-config table td {
            padding: 10px;
            padding-left: 0;
    }
    #product-videos-config table tbody tr:hover {
        background-color: #f7f7f7;
    }

    #product-videos-config td.delete i:hover {
        cursor: pointer;
        color: #dc3545;
    }

    #add-attribute-btn:hover {
        cursor: pointer;
        color: #ff6000;
    }

    #product-videos-config tr td:first-of-type {
        vertical-align: top;
    }
</style>
<form method="POST" action="{$post_action}">
<div id="product-videos-config" class="panel product-tab">
    <h3>{l s='Default Attributes'}</h3>
    <p>Attributes here will get added to all embedded content generated for a video</p>

    <div class="form-group">
		<table>
            <thead>
                <tr>
                    <th style="width:20%"><strong>Name</strong></th>
                    <th><strong>Value</strong></th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="attributes-container">
                {foreach from=$attributes item=attr}
                    <tr>
                        <td style="width:20%">
                            <input type="text" value="{$attr.name}" class="form-control" name="attribute_names[]"/>
                        </td>
                        <td>
                            <input type="text" value="{$attr.value}" class="form-control" name="attribute_values[]"/>
                        </td>
                        <td class="delete">
                            <i class="process-icon-delete" title="{l s='Remove Attribute'}" onclick="deleteAttribute(event)"></i>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
        <div id="add-attribute-btn" style="width: 100%; text-align: center; display: block;" onclick="addNewAttribute()">
            <i class="process-icon-new"></i>
            {l s="New Attribute"}
        </div>
	</div>

    <div class="panel-footer">
		<button type="submit" name="submitStoreConf" class="btn btn-default pull-right" ><i class="process-icon-save"></i> {l s='Save'}</button>
	</div>
</div>
</form>

<script type="text/javascript">
    function addNewAttribute() {
        var container = document.getElementById('attributes-container');
        if (container == null) {
            alert('Add New Attribute Failed: container not found');
            return;
        }

        var html = '<td><input type="text" value="" class="form-control" name="attribute_names[]"/></td><td><input type="text" class="form-control" name="attribute_values[]" /></td><td class="delete"><i class="process-icon-delete" title="{l s="Remove Video"}" onclick="deleteAttribute(event)"></i></td>';
        var el = document.createElement('tr');
        el.innerHTML = html;

        container.appendChild(el);
    }

    function deleteAttribute(e) {
        var container = document.getElementById('attributes-container');
        if (container == null) {
            alert('Removing Attribute Failed: container not found');
            return;
        }

        var row = e.currentTarget.parentNode.parentNode;

        if (confirm("{l s='Are you sure you want to delete this attribute?'}"))
        {
            container.removeChild(row);
        }
    }

</script>