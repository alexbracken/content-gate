( function( blocks, element, blockEditor ) {
    var el = element.createElement;
    var InnerBlocks = blockEditor.InnerBlocks;
    var useBlockProps = blockEditor.useBlockProps;
    
    blocks.registerBlockType('cg/content-gate', {
        title: 'Content Gate',
        icon: 'lock',
        category: 'widgets',
        
        edit: function() {
            var blockProps = useBlockProps({
                className: 'cg-content-gate-wrapper',
                style: { 
                    border: '1px dashed #5b9dd9', 
                    background: 'rgba(91, 157, 217, 0.1)',
                    padding: '20px',
                    marginBottom: '20px'
                }
            });

            // Define allowed blocks for the form area
            var ALLOWED_BLOCKS = [
                'core/paragraph', 
                'core/heading', 
                'core/image', 
                'core/columns', 
                'core/group',
                'core/cover',
                'core/button',
                'core/buttons',
                'core/html'
            ];

            // Default template
            var DEFAULT_TEMPLATE = [
                ['core/heading', { 
                    content: 'This content is protected',
                    level: 2,
                    className: 'cg-heading'
                }],
                ['core/paragraph', { 
                    content: 'Please enter your information to continue reading.',
                    className: 'cg-description'
                }]
            ];
            
            return el(
                'div',
                blockProps,
                el(
                    'div',
                    { className: 'cg-gate-notice' },
                    el('span', { 
                        style: { 
                            display: 'flex',
                            alignItems: 'center',
                            color: '#5b9dd9',
                            marginBottom: '10px',
                            fontStyle: 'italic'
                        } 
                    }, 
                        el('span', { 
                            className: 'dashicons dashicons-lock',
                            style: { marginRight: '5px' }
                        }),
                        'Content Gate - All content below this block will be hidden until form submission'
                    )
                ),
                el(InnerBlocks, {
                    allowedBlocks: ALLOWED_BLOCKS,
                    template: DEFAULT_TEMPLATE,
                    templateLock: false
                })
            );
        },
        
        save: function() {
            return el(
                'div',
                { className: 'cg-content-gate' },
                el(InnerBlocks.Content),
                el('form', { 
                    className: 'cg-gate-form',
                    action: '#'
                },
                el('div', { className: 'cg-form-row' },
                    el('div', { className: 'cg-form-group' },
                        el('label', { htmlFor: 'cg-name' }, 'Name'),
                        el('input', { 
                            type: 'text',
                            id: 'cg-name',
                            name: 'name',
                            required: true
                        })
                    ),
                    el('div', { className: 'cg-form-group' },
                        el('label', { htmlFor: 'cg-email' }, 'Email'),
                        el('input', { 
                            type: 'email',
                            id: 'cg-email',
                            name: 'email',
                            required: true
                        })
                    ),
                    el('div', { className: 'cg-form-submit' },
                        el('button', { 
                            type: 'submit',
                            className: 'button button-primary'
                        }, 'Continue Reading')
                    )
                ))
            );
        }
    });
})( 
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor || window.wp.editor 
);