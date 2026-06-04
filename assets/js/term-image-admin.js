/* global wp */
(function ($) {
	$(document).on('click', '.wtb-upload-image', function (e) {
		e.preventDefault();
		var $btn   = $(this);
		var $field = $btn.closest('.wtb-term-image-field');

		var frame = wp.media({
			title:    'Select Term Image',
			button:   { text: 'Use this image' },
			multiple: false,
			library:  { type: 'image' },
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			var url = (attachment.sizes && attachment.sizes.thumbnail)
				? attachment.sizes.thumbnail.url
				: attachment.url;

			$field.find('input[name="wtb_image_id"]').val(attachment.id);
			$field.find('.wtb-image-preview img').attr('src', url);
			$field.find('.wtb-image-preview').show();
			$field.find('.wtb-remove-image').show();
			$btn.text('Change Image');
		});

		frame.open();
	});

	$(document).on('click', '.wtb-remove-image', function (e) {
		e.preventDefault();
		var $field = $(this).closest('.wtb-term-image-field');
		$field.find('input[name="wtb_image_id"]').val('');
		$field.find('.wtb-image-preview').hide();
		$(this).hide();
		$field.find('.wtb-upload-image').text('Upload Image');
	});
}(jQuery));
