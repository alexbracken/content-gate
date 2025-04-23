( function( blocks, element, blockEditor ) {
    var el = element.createElement;
    var InnerBlocks = blockEditor.InnerBlocks;
    var useBlockProps = blockEditor.useBlockProps;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var ToggleControl = wp.components.ToggleControl;

    blocks.registerBlockType( 'cg/content-gate', {
        title: 'Content Gate',
        icon: 'lock',
        category: 'widgets',
        attributes: {
            useCustomGate: {
                type: 'boolean',
                default: false
            }
        },
        edit: function(props) {
            var attributes = props.attributes;
            var blockProps = useBlockProps({
                className: 'cg-content-gate-editor',
                style: { border: '1px dashed #ddd', padding: '10px' }
            });
            
            // Define allowed blocks for the form area
            var ALLOWED_FORM_BLOCKS = [
                'core/paragraph', 
                'core/heading', 
                'core/image', 
                'core/columns', 
                'core/group',
                'core/cover',
                'core/button',
                'core/buttons'
            ];
            
            // Define allowed blocks for the gated content
            var ALLOWED_CONTENT_BLOCKS = null; // Allow all blocks in content
            
            // Templates
            var DEFAULT_FORM_TEMPLATE = [
                ['core/heading', { 
                    content: 'This content is protected.',
                    level: 4,
                    className: 'cg-heading'
                }],
                ['core/paragraph', { 
                    content: 'Please enter your information to continue.',
                    className: 'cg-subheading'
                }]
            ];
            
            return el(
                'div',
                blockProps,
                el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: 'Content Gate Settings', initialOpen: true },
                        el(
                            ToggleControl,
                            {
                                label: 'Use Custom Form Layout',
                                help: attributes.useCustomGate ? 
                                      'Using custom block layout for the form area' : 
                                      'Using default form layout',
                                checked: attributes.useCustomGate,
                                onChange: function() {
                                    props.setAttributes({ 
                                        useCustomGate: !attributes.useCustomGate 
                                    });
                                }
                            }
                        )
                    )
                ),
                
                // Form area - either custom blocks or default form preview
                el(
                    'div',
                    { className: 'cg-form-area' },
                    attributes.useCustomGate ? 
                        el(
                            InnerBlocks,
                            { 
                                allowedBlocks: ALLOWED_FORM_BLOCKS,
                                templateLock: false,
                                template: DEFAULT_FORM_TEMPLATE,
                                className: 'cg-form-blocks'
                            }
                        ) :
                        el(
                            'div',
                            { className: 'cg-preview' },
                            el('h4', { className: 'cg-heading' }, 'This content is protected.'),
                            el('p', { className: 'cg-subheading' }, 'Please enter your information to continue.')
                        )
                ),
                
                // Default form controls (always shown)
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
                        el('button', { 
                            className: 'button button-primary', 
                            disabled: true 
                        }, 'Submit')
                    )
                ),
                
                // Gated content area
                el(
                    'div',
                    { style: { marginTop: '10px', padding: '10px', background: '#f8f8f8' } },
                    el('p', {}, 'Hidden content will appear here after form submission:'),
                    el(
                        InnerBlocks,
                        { 
                            allowedBlocks: ALLOWED_CONTENT_BLOCKS,
                            templateLock: false
                        }
                    )
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