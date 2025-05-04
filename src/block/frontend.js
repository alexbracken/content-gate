document.addEventListener( 'DOMContentLoaded', function () {
	const gateBlocks = document.querySelectorAll( '.content-gate-block' );

	function getCookie( name ) {
		const value = `; ${ document.cookie }`;
		const parts = value.split( `; ${ name }=` );
		if ( parts.length === 2 ) {
			return parts.pop().split( ';' ).shift();
		}
	}

	function hideContent( block ) {
		const content = block.querySelector( '.content-gate-content' );
		const form = block.querySelector( '.content-gate-form' );
		if ( content && form ) {
			content.style.display = 'none';
			form.style.display = 'block';
		}
	}

	function showContent( block ) {
		const content = block.querySelector( '.content-gate-content' );
		const form = block.querySelector( '.content-gate-form' );
		if ( content && form ) {
			content.style.display = 'block';
			form.style.display = 'none';
		}
	}

	gateBlocks.forEach( ( block ) => {
		const accessToken = getCookie( 'content_gate_access' );
		if ( ! accessToken ) {
			hideContent( block );
		}

		const form = block.querySelector( '.content-gate-form-fields' );
		if ( form && window.contentGateData?.recaptchaSiteKey ) {
			// Add reCAPTCHA container
			const recaptchaDiv = document.createElement( 'div' );
			recaptchaDiv.className = 'g-recaptcha';
			recaptchaDiv.dataset.sitekey =
				window.contentGateData.recaptchaSiteKey;
			form.insertBefore( recaptchaDiv, form.querySelector( 'button' ) );
		}

		form?.addEventListener( 'submit', async function ( e ) {
			e.preventDefault();

			const formData = new FormData( form );
			formData.append( 'action', 'content_gate_submit' );
			formData.append(
				'content_gate_nonce',
				window.contentGateData?.nonce || ''
			);

			try {
				const response = await fetch( window.ajaxurl, {
					method: 'POST',
					body: formData,
					credentials: 'same-origin',
				} );

				const data = await response.json();

				if ( data.success ) {
					showContent( block );
					// Reset reCAPTCHA if it exists
					if ( window.grecaptcha ) {
						grecaptcha.reset();
					}
				} else {
					alert(
						data.data || 'An error occurred. Please try again.'
					);
				}
			} catch ( error ) {
				console.error( 'Content Gate submission error:', error );
				alert( 'An error occurred. Please try again.' );
			}
		} );
	} );
} );
