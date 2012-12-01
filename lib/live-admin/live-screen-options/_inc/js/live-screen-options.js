var postboxes;

(function($) {
	postboxes = {
		add_postbox_toggles : function(page, args) {
			var self = this;

			$('.postbox-live a.dismiss').bind('click.postboxes', function(e) {
				var hide_id = $(this).parents('.postbox-live').attr('id') + '-hide';
				$( '#' + hide_id ).prop('checked', false).triggerHandler('click');
				return false;
			});

			$('.hide-postbox-tog').bind('click.postboxes', function() {
				var box = $(this).val();

				if ( $(this).prop('checked') ) {
					$('#' + box).show();
					if ( $.isFunction( postboxes.pbshow ) )
						self.pbshow( box );
				} else {
					$('#' + box).hide();
					if ( $.isFunction( postboxes.pbhide ) )
						self.pbhide( box );
				}
				self.save_state(page);
			});

		},

		save_state : function(page) {
			var closed = $('.postbox-live').filter('.closed').map(function() { return this.id; }).get().join(','),
				hidden = $('.postbox-live').filter(':hidden').map(function() { return this.id; }).get().join(',');

			$.post(ajaxurl, {
				action: 'closed-postboxes',
				closed: closed,
				hidden: hidden,
				closedpostboxesnonce: jQuery('#closedpostboxesnonce').val(),
				page: page
			});
		},

		/* Callbacks */
		pbshow : false,

		pbhide : false
	};

}(jQuery));

jQuery(document).ready( function ($) {
    $('.toggle-screen-options').click( function() {
        $('#live-admin-screen-options').toggleClass( 'expanded' ).toggleClass( 'collapsed' );
    });
});