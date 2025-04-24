import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType('content-gate/registration', {
    edit: function() {
        const blockProps = useBlockProps();
        
        return (
            <div { ...blockProps }>
                <div className="content-gate-form">
                    <h3>Register to view content</h3>
                    <div className="content-gate-preview-form">
                        <input type="text" disabled placeholder="Name" />
                        <input type="email" disabled placeholder="Email" />
                        <button disabled>Register</button>
                    </div>
                </div>
            </div>
        );
    },
    
    save: function() {
        const blockProps = useBlockProps.save();
        
        return (
            <div { ...blockProps }>
                <div className="content-gate-form">
                    <h3>Register to view content</h3>
                    <form id="content-gate-form">
                        <input type="text" name="name" required placeholder="Name" />
                        <input type="email" name="email" required placeholder="Email" />
                        <input type="hidden" name="nonce" value={wpNonce} />
                        <button type="submit">Register</button>
                    </form>
                </div>
                <div className="content-gate-content" style={{display: 'none'}}>
                    {/* Protected content will be moved here via JavaScript */}
                </div>
            </div>
        );
    }
});
