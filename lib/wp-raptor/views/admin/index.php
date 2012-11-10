<div class="wrap raptor-wrap">
    <div class="raptor-settings-form">
        <h2>WP Raptor Options</h2>

        <form method="post" action="" autocomplete="off">

            <?php $this->options->save(); ?>
            <?php settings_fields(RaptorOptions::OPTIONS); ?>

            <div id="raptor-options-tabs">
                <ul>
                    <li><a href="#raptor-options-general">General</a></li>
                    <li><a href="#raptor-options-editor">Editor</a></li>
                </ul>
                <!-- General -->
                <div id="raptor-options-general">
                    <strong>Use Raptor Editor to</strong>:
                    <br/>
                    <label for="raptor-allow-in-place-editing">
                        <input id="raptor-allow-in-place-editing" type="checkbox" value="1" name="<?php echo RaptorOptions::INDEX_ALLOW_IN_PLACE_EDITING; ?>" <?php if ($this->options->allowInPlaceEditing()): ?>checked="checked"<?php endif; ?> />
                        Edit posts on the front end
                    </label>
                    <br/>
                    <label for="raptor-raptorize-quickpress">
                        <input id="raptor-raptorize-quickpress" type="checkbox" value="1"name="<?php echo RaptorOptions::INDEX_RAPTORIZE_QUICKPRESS; ?>" <?php if ($this->options->raptorizeQuickpress()): ?>checked="checked"<?php endif; ?> />
                        Edit posts on the dashboard (quickpress)
                    </label>
                    <br/>
                    <label for="raptor-admin-post-editing">
                        <input id="raptor-admin-post-editing" type="checkbox" value="1" name="<?php echo RaptorOptions::INDEX_RAPTORIZE_ADMIN_EDITING; ?>" <?php if ($this->options->raptorizeAdminEditing()): ?>checked="checked"<?php endif; ?> />
                        Edit posts on the main admin page
                    </label>
                    <hr/>
                    <br/>
                    <label for="raptor-admin-allow-additional-classes">
                        <input id="raptor-admin-allow-additional-classes" type="checkbox" value="1" name="<?php echo RaptorOptions::ALLOW_ADDITIONAL_EDITOR_SELECTORS; ?>" <?php if ($this->options->allowAdditionalEditorSelectors()): ?>checked="checked"<?php endif; ?> />
                        Edit elements selected by the following jQuery selectors (<em>admin pages only</em>)
                    </label>
                    <br/>
                    <input <?php if (!$this->options->allowAdditionalEditorSelectors()) echo 'disabled="disabled"'; ?> id="raptor-admin-additional-classes" type="text" name="<?php echo RaptorOptions::ADDITIONAL_EDITOR_SELECTORS; ?>" value="<?php echo $this->options->additionalEditorSelectors(); ?>" />
                    <br/>
                    <em>What is a <a href="http://www.tutorialspoint.com/jquery/jquery-selectors.htm" target="_blank">jQuery Selector</a>?</em>
                    <script type="text/javascript">
                        (function($) {
                            $('#raptor-admin-allow-additional-classes').change(function() {
                                $('#raptor-admin-additional-classes').attr('disabled', $(this).is(':checked') ? null : 'disabled');
                            });
                        })(jQuery);
                    </script>
                </div>
                <div id="raptor-options-editor">
                    <label for="raptor-admin-resize-images">
                        <input id="raptor-admin-resize-images" type="checkbox" value="1" name="<?php echo RaptorOptions::RESIZE_IMAGES_AUTOMATICALLY; ?>" <?php if ($this->options->resizeImagesAutomatically()): ?>checked="checked"<?php endif; ?> />
                        Automatically resize inserted image when the image is too large (<em>Editing-in-place only</em>)
                    </label>
                </div>
            </div>
            <script type="text/javascript">
                jQuery(function() {
                    jQuery('#raptor-options-tabs').tabs({
                        cookie: {
                            expires: 10
                        }
                    });
                });
            </script>
            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
            </p>
        </form>
    </div>

    <div class="raptor-settings-information">
        <h2>WP Raptor's Roots</h2>
        <p>
            Wordpress Plugin created by Michael Robinson, who lives at <a target="_blank" href="http://pagesofinterest.net/?utm_source=wp-raptor&utm_medium=admin-index&utm_content=michael-robinson&utm_campaign=wp-raptor" title="Michael Robinson!">pagesofinterest.net</a>, and uses Twitter.
            <br/>
            <br/>
            <a href="https://twitter.com/pagesofinterest" class="twitter-follow-button" data-show-count="false">Follow @pagesofinterest</a>
        </p>
        <hr/>
        <p>
            WP Raptor was built with <a target="_blank" href="http://raptor-editor.com/?utm_source=wp-raptor&utm_medium=admin-index&utm_content=raptor-editor&utm_campaign=wp-raptor">Raptor Editor</a>, this generation's WYSIWYG editor. Raptor lives between <a href="http://raptor-editor.com/" title="Raptor Editor">raptor-editor.com</a>, and Twitter.
            <br/>
            <br/>
            <a href="https://twitter.com/raptoreditor" class="twitter-follow-button" data-show-count="false">Follow @raptoreditor</a>
        </p>
        <hr/>
        <p>
            Raptor Editor in turn was built by the wonderful team at <a target="_blank" href="http://www.panmedia.co.nz/?utm_source=wp-raptor&utm_medium=admin-index&utm_content=panmedia&utm_campaign=wp-raptor">PANmedia</a>.
            <br/>
            Thanks PAN!
            <br/>
            <br/>
            <a href="https://twitter.com/panmedianz" class="twitter-follow-button" data-show-count="false">Follow @PANmediaNZ</a>
        </p>
    </div>

</div>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>


















