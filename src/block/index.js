import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

registerBlockType( 'content-gate/main', {
	title: __( 'Content Gate', 'content-gate' ),
	description: __( 'Gate content behind a name/email form', 'content-gate' ),
	icon: 'lock',
	category: 'widgets',
	supports: {
		html: false,
	},
	attributes: {
		content: {
			type: 'string',
			source: 'html',
			selector: '.content-gate-content',
		},
	},

	edit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps( {
			className: 'content-gate-block',
		} );

		return (
			<div { ...blockProps }>
				<div className="content-gate-notice">
					<span>⚠️ Content below this point will be gated</span>
				</div>
				<div className="content-gate-content">
					<InnerBlocks />
				</div>
			</div>
		);
	},

	save( { attributes } ) {
		const blockProps = useBlockProps.save( {
			className: 'content-gate-block',
		} );

		return (
			<div { ...blockProps }>
				<div
					className="content-gate-form"
					style={ { display: 'none' } }
				>
					<h3>{ __( 'Continue Reading', 'content-gate' ) }</h3>
					<p>
						{ __(
							'Please enter your details to continue reading this content.',
							'content-gate'
						) }
					</p>
					<form className="content-gate-form-fields">
						<input
							type="text"
							name="name"
							placeholder={ __( 'Your Name', 'content-gate' ) }
							required
						/>
						<input
							type="email"
							name="email"
							placeholder={ __( 'Your Email', 'content-gate' ) }
							required
						/>
						<label>
							<input type="checkbox" name="remember" />
							{ __(
								'Remember me for future articles',
								'content-gate'
							) }
						</label>
						<button type="submit">
							{ __( 'Continue Reading', 'content-gate' ) }
						</button>
					</form>
				</div>
				<div className="content-gate-content">
					<InnerBlocks.Content />
				</div>
			</div>
		);
	},
} );
