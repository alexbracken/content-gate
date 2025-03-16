( function( blocks, element, blockEditor ) {
    var el = element.createElement;
    var InnerBlocks = blockEditor.InnerBlocks;

    blocks.registerBlockType( 'cg/content-gate', {
        title: 'Content Gate',
        icon: 'lock',
        category: 'widgets',
        edit: function() {
            return el(
                'div',
                { className: 'cg-content-gate-editor', style: { border: '1px dashed #ddd', padding: '10px' } },
                el( 'p', {}, 'Content Gate: The content below will be hidden until the form is submitted.' ),
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
