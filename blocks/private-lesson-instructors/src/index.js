import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, __experimentalNumberControl as NumberControl } from '@wordpress/components';

wp.blocks.registerBlockType('bailaya/private-lesson-instructors', {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { override_id, locale, cache_ttl, book_base_url } = attributes;

        const blockProps = useBlockProps();

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Settings', 'bailaya')} initialOpen={true}>
                        <TextControl
                            label={__('Override Studio ID', 'bailaya')}
                            value={override_id || ''}
                            onChange={(val) => setAttributes({ override_id: val })}
                            placeholder="studio-xyz"
                        />
                        <TextControl
                            label={__('Locale', 'bailaya')}
                            value={locale || ''}
                            onChange={(val) => setAttributes({ locale: val })}
                            placeholder="en"
                            help={__('Language code for bio text, e.g. "en" or "es".', 'bailaya')}
                        />
                        <TextControl
                            label={__('Booking Base URL', 'bailaya')}
                            value={book_base_url || ''}
                            onChange={(val) => setAttributes({ book_base_url: val })}
                            placeholder="https://www.bailaya.com/en/book/private-lesson/"
                            help={__('The instructor ID will be appended to this URL.', 'bailaya')}
                        />
                        <NumberControl
                            label={__('Cache TTL (seconds)', 'bailaya')}
                            min={0}
                            value={Number(cache_ttl || 0)}
                            onChange={(val) => setAttributes({ cache_ttl: Number(val || 0) })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    <div style={{ opacity: 0.6, fontStyle: 'italic' }}>
                        {__('Private lesson instructors will render here…', 'bailaya')}
                    </div>
                </div>
            </>
        );
    },
    save: () => null, // dynamic block
});
