import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

registerBlockType('content-gate/main', {
    title: __('Content Gate', 'content-gate'),
    description: __('Gate content behind a name/email form', 'content-gate'),
    icon: 'lock',
    category: 'widgets',
    supports: {
        html: false,
        align: ['wide', 'full'],
    },
    attributes: {
        content: {
            type: 'string',
            source: 'html',
            selector: '.content-gate-content',
        },
    },

    edit({ attributes, setAttributes }) {
        const blockProps = useBlockProps({
            className: 'content-gate-block wp-block-group',
        });

        return (
            <div {...blockProps}>
                <div className="content-gate-divider">
                    <hr className="content-gate-line" />
                    <div className="content-gate-icon">
                        <span className="dashicons dashicons-lock"></span>
                        {__('Content Gate', 'content-gate')}
                    </div>
                </div>
                <div className="content-gate-content">
                    <InnerBlocks />
                </div>
            </div>
        );
    },

    save({ attributes }) {
        const blockProps = useBlockProps.save({
            className: 'content-gate-block wp-block-group',
        });

        return (
            <div {...blockProps}>
                <div className="content-gate-form" style={{ display: 'none' }}>
                    <h3>{__('Continue Reading', 'content-gate')}</h3>
                    <p>
                        {__('Please enter your details to continue reading this content.', 'content-gate')}
                    </p>
                    <form className="content-gate-form-fields">
                        <input
                            type="text"
                            name="name"
                            placeholder={__('Your Name', 'content-gate')}
                            required
                        />
                        <input
                            type="email"
                            name="email"
                            placeholder={__('Your Email', 'content-gate')}
                            required
                        />
                        <label className="content-gate-remember">
                            <input type="checkbox" name="remember" />
                            <span>{__('Remember me for future articles', 'content-gate')}</span>
                        </label>
                        <button type="submit" className="wp-element-button">
                            {__('Continue Reading', 'content-gate')}
                        </button>
                    </form>
                </div>
                <div className="content-gate-content">
                    <InnerBlocks.Content />
                </div>
            </div>
        );
    },
});
