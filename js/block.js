const { registerBlockType } = wp.blocks;
const { InnerBlocks } = wp.blockEditor;

registerBlockType('simple-content-gate/gate-block', {
    title: 'Content Gate',
    icon: 'lock',
    category: 'common',
    description: 'Add a content gate that requires user details to view content',
    
    edit: function(props) {
        return (
            <div className="content-gate-block">
                <div className="content-gate-notice">
                    <span>⚠️ Content below this point will be gated</span>
                </div>
                <InnerBlocks />
            </div>
        );
    },

    save: function(props) {
        return <InnerBlocks.Content />;
    }
});