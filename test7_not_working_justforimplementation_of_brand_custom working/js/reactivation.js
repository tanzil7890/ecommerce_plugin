jQuery(document).ready(function($) {
    if (pcm_data.is_reactivated === 'true') {
        // Create and append modal HTML
        var modalHtml = `
            <div id="pcm-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.4);">
                <div style="background-color:#fefefe; margin:15% auto; padding:20px; border:1px solid #888; width:50%;">
                    <h2>Product Collection Manager Reactivated</h2>
                    <p>Do you want to restore your previous collections or start fresh?</p>
                    <button id="pcm-restore-collections" class="button button-primary">Restore Collections</button>
                    <button id="pcm-clear-collections" class="button">Start Fresh</button>
                </div>
            </div>
        `;
        $('body').append(modalHtml);

        // Show the modal immediately
        $('#pcm-modal').show();

        // Handle restore collections
        $('#pcm-restore-collections').on('click', function() {
            $.ajax({
                url: pcm_data.ajax_url,
                type: 'POST',
                data: {
                    action: 'pcm_restore_collections',
                    nonce: pcm_data.nonce
                },
                success: function(response) {
                    alert(response.data.message);
                    $('#pcm-modal').hide();
                    location.reload(); // Reload the page to reflect changes
                }
            });
        });

        // Handle clear collections
        $('#pcm-clear-collections').on('click', function() {
            $.ajax({
                url: pcm_data.ajax_url,
                type: 'POST',
                data: {
                    action: 'pcm_clear_collections',
                    nonce: pcm_data.nonce
                },
                success: function(response) {
                    alert(response.data.message);
                    $('#pcm-modal').hide();
                    location.reload(); // Reload the page to reflect changes
                }
            });
        });
    }
});