( function( blocks, element, blockEditor ) {
    var el = element.createElement;
    var InnerBlocks = blockEditor.InnerBlocks;
    var TextControl = wp.components.TextControl;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;

    blocks.registerBlockType( 'cg/content-gate', {
        title: 'Content Gate',
        icon: 'lock',
        category: 'widgets',
        attributes: {
            headingText: {
                type: 'string',
                default: 'This content is protected.'
            },
            subheadText: {
                type: 'string',
                default: 'Please enter your information to continue.'
            }
        },
        edit: function(props) {
            var attributes = props.attributes;
            
            return el(
                'div',
                { className: 'cg-content-gate-editor', style: { border: '1px dashed #ddd', padding: '10px' } },
                el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: 'Content Gate Settings', initialOpen: true },
                        el(
                            TextControl,
                            {
                                label: 'Heading Text',
                                value: attributes.headingText,
                                onChange: function(newHeading) {
                                    props.setAttributes({ headingText: newHeading });
                                }
                            }
                        ),
                        el(
                            TextControl,
                            {
                                label: 'Subheading Text',
                                value: attributes.subheadText,
                                onChange: function(newSubhead) {
                                    props.setAttributes({ subheadText: newSubhead });
                                }
                            }
                        )
                    )
                ),
                el(
                    'div',
                    { className: 'cg-preview' },
                    el('h4', { className: 'cg-heading' }, attributes.headingText),
                    el('p', { className: 'cg-subheading' }, attributes.subheadText),
                    el(
                        'div',
                        { className: 'cg-form-preview', style: { display: 'flex', gap: '8px', marginBottom: '15px' } },
                        el('div', { style: { flex: '1' } }, 
                            el('label', { style: { display: 'block', marginBottom: '4px' } }, 'Name'), 
                            el('input', { type: 'text', disabled: true, style: { width: '100%' } })
                        ),
                        el('div', { style: { flex: '1' } }, 
                            el('label', { style: { display: 'block', marginBottom: '4px' } }, 'Email'), 
                            el('input', { type: 'text', disabled: true, style: { width: '100%' } })
                        ),
                        el('div', { style: { alignSelf: 'flex-end' } }, 
                            el('input', { type: 'button', value: 'Submit', disabled: true })
                        )
                    )
                ),
                el(
                    'div',
                    { style: { marginTop: '10px', padding: '10px', background: '#f8f8f8' } },
                    el('p', {}, 'Hidden content will appear here after form submission:'),
                    el(InnerBlocks)
                )
            );
        },
        save: function() {
            return el( InnerBlocks.Content );
        }
    });
} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor || window.wp.editor
);