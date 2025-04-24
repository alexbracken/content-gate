import { useBlockProps } from '@wordpress/block-editor';

export default function Save({ attributes }) {
    const { heading, subheading } = attributes;
    const blockProps = useBlockProps.save();

    return (
        <div { ...blockProps }>
            <div className="content-gate-form">
                <h3>{heading}</h3>
                <p>{subheading}</p>
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
