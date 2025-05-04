import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

registerBlockType('gcb/gated-content', {
    title: __('Gated Content', 'gcb'),
    icon: 'lock',
    category: 'widgets',
    attributes: {
        formTitle: { type: 'string', default: __('Unlock this content', 'gcb') },
        nameLabel: { type: 'string', default: __('Name', 'gcb') },
        emailLabel: { type: 'string', default: __('Email', 'gcb') },
        rememberLabel: { type: 'string', default: __('Remember me for 30 days', 'gcb') },
        submitLabel: { type: 'string', default: __('Unlock', 'gcb') },
        privacyNotice: { type: 'string', default: __('Your information will not be used for any other purpose.', 'gcb') },
        gatedContent: { type: 'string', source: 'html', selector: '.gcb-gated-section' }
    },
    supports: {
        multiple: false
    },
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const blockProps = useBlockProps();

        return (
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody title={__('Form Settings', 'gcb')}>
                        <TextControl
                            label={__('Form Title', 'gcb')}
                            value={attributes.formTitle}
                            onChange={(v) => setAttributes({ formTitle: v })}
                        />
                        <TextControl
                            label={__('Name Label', 'gcb')}
                            value={attributes.nameLabel}
                            onChange={(v) => setAttributes({ nameLabel: v })}
                        />
                        <TextControl
                            label={__('Email Label', 'gcb')}
                            value={attributes.emailLabel}
                            onChange={(v) => setAttributes({ emailLabel: v })}
                        />
                        <TextControl
                            label={__('Remember Me Label', 'gcb')}
                            value={attributes.rememberLabel}
                            onChange={(v) => setAttributes({ rememberLabel: v })}
                        />
                        <TextControl
                            label={__('Submit Button Label', 'gcb')}
                            value={attributes.submitLabel}
                            onChange={(v) => setAttributes({ submitLabel: v })}
                        />
                        <TextareaControl
                            label={__('Privacy Notice', 'gcb')}
                            value={attributes.privacyNotice}
                            onChange={(v) => setAttributes({ privacyNotice: v })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div className="gcb-gate-form-preview">
                    <h4>{attributes.formTitle}</h4>
                    <label>{attributes.nameLabel}</label>
                    <input type="text" disabled />
                    <label>{attributes.emailLabel}</label>
                    <input type="email" disabled />
                    <label>
                        <input type="checkbox" disabled /> {attributes.rememberLabel}
                    </label>
                    <button disabled>{attributes.submitLabel}</button>
                    <div className="gcb-privacy">{attributes.privacyNotice}</div>
                </div>
                <div className="gcb-gated-section">
                    <RichText
                        tagName="div"
                        value={attributes.gatedContent}
                        onChange={(v) => setAttributes({ gatedContent: v })}
                        placeholder={__('Add gated content here...', 'gcb')}
                    />
                </div>
            </div>
        );
    },
    save: (props) => {
        const { attributes } = props;
        return (
            <div {...useBlockProps.save()}>
                <div
                    className="gcb-gate-form"
                    data-form-title={attributes.formTitle}
                    data-name-label={attributes.nameLabel}
                    data-email-label={attributes.emailLabel}
                    data-remember-label={attributes.rememberLabel}
                    data-submit-label={attributes.submitLabel}
                    data-privacy-notice={attributes.privacyNotice}
                ></div>
                <div className="gcb-gated-section" style={{ display: 'none' }}>
                    <RichText.Content value={attributes.gatedContent} />
                </div>
            </div>
        );
    }
});
