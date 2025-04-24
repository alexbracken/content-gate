( function( blocks, element, blockEditor ) {
    var el = element.createElement;
    var InnerBlocks = blockEditor.InnerBlocks;
    var useBlockProps = blockEditor.useBlockProps;

    blocks.registerBlockType('cg/content-gate', {
        title: 'Content Gate',
        icon: 'lock',
        category: 'widgets',
        edit: function() {
            return el(
                'div',
                useBlockProps({ className: 'cg-content-gate-wrapper', style: { border: '1px dashed #5b9dd9', padding: '20px' } }),
                el('p', {}, 'Content below will be gated.'),
                el(InnerBlocks)
            );
        },
        save: function() {
            return el(
                'div',
                { className: 'cg-content-gate' },
                el(InnerBlocks.Content)
            );
        }
    });
} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor || window.wp.editor
);