jQuery(document).ready(function($) {

	var gilTopTipsMediaUploader;
	var gilTopTipsImageContainer = $('#giltoptips_image_preview');
	var gilTopTipsImageInput     = $('#giltoptips_image');

	// Function to open the media uploader

	function gilTopTipsOpenMediaUploader() {
		if ( gilTopTipsMediaUploader ) {
			gilTopTipsMediaUploader.open();
			return;
		}

		gilTopTipsMediaUploader = wp.media({
			title: 'Select Image',
			button: {
			text: 'Choose Image'
			},
			multiple: false // Set to true if you want to allow multiple image selections
		});

		gilTopTipsMediaUploader.on('select', function() {
			var attachment = gilTopTipsMediaUploader.state().get('selection').first().toJSON();

			// Display the selected image
			//imageContainer.html('<img src="' + attachment.url + '" alt="Selected Image" style="max-width: 100%;">');
			gilTopTipsImageContainer.attr( 'src', attachment.url );

			// Set the image URL in the input field
			gilTopTipsImageInput.val( attachment.url );
		});
	gilTopTipsMediaUploader.open();
	}

	// Function to handle the remove button click event
	function gilTopTipsRemoveImage() {
		let elHeight = gilTopTipsImageContainer.height();
		// Clear the selected image
		gilTopTipsImageContainer.attr( 'src', giltoptips_data.default_image ).height( elHeight );

		// Clear the image URL in the input field
		gilTopTipsImageInput.val( giltoptips_data.default_image );
	}

	// Attach click event handlers to the buttons
	$('#giltoptips_upload_image_button').click( gilTopTipsOpenMediaUploader );
	$('#giltoptips_revert_image_button').click( gilTopTipsRemoveImage );

});
