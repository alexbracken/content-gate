( function( blocks, element, blockEditor, components ) {
    var el = element.createElement;
    var InnerBlocks = blockEditor.InnerBlocks;

    blocks.registerBlockType( 'cg/content-gate', {
        title: 'Content Gate',
        icon: 'lock',
        category: 'widgets',
        attributes: {
            contactFormShortcode: {
                type: 'string',
                default: '[contact-form-7 id="1" title="Contact form 1"]'
            }
        },
        edit: function( props ) {
            function onChangeShortcode( event ) {
                props.setAttributes( { contactFormShortcode: event.target.value } );
            }
            return el(
                'div',
                { className: 'cg-content-gate-editor', style: { border: '1px dashed #ddd', padding: '10px' } },
                el( 'p', {}, 'Contact Form 7 Shortcode:' ),
                el( 'input', {
                    type: 'text',
                    value: props.attributes.contactFormShortcode,
                    onChange: onChangeShortcode,
                    style: { width: '100%', marginBottom: '10px' }
                }),
                el( 'p', {}, 'Gated Content (visible after form submission):' ),
                el( InnerBlocks )
            );
        },
        save: function() {
            // Frontend markup is rendered via PHP (render_callback).
            return el( InnerBlocks.Content );
        }
    });
} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor || window.wp.editor,
    window.wp.components
);
