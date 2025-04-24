import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
    const { heading, subheading } = attributes;
    const blockProps = useBlockProps();

    return (
        <>
            <InspectorControls>
                <PanelBody title="Form Settings">
                    <TextControl
                        label="Heading"
                        value={heading}
                        onChange={(value) => setAttributes({ heading: value })}
                    />
                    <TextControl
                        label="Subheading"
                        value={subheading}
                        onChange={(value) => setAttributes({ subheading: value })}
                    />
                </PanelBody>
            </InspectorControls>
            <div { ...blockProps }>
                <div className="content-gate-form">
                    <h3>{heading}</h3>
                    <p>{subheading}</p>
                    <div className="content-gate-preview-form">
                        <input type="text" disabled placeholder="Name" />
                        <input type="email" disabled placeholder="Email" />
                        <button disabled>Register</button>
                    </div>
                </div>
            </div>
        </>
    );
}
