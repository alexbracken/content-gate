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
                el( 'p', {}, 'Content Gate: The content below will be hidden until the form is submitted.' ),
                el( 'p', { style: { fontWeight: 'bold' } }, attributes.headingText ),
                el( 'p', {}, attributes.subheadText ),
                el( InnerBlocks )
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