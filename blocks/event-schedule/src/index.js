import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, __experimentalNumberControl as NumberControl } from '@wordpress/components';

wp.blocks.registerBlockType('bailaya/event-schedule', {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { override_id, locale, from, hide_location, cache_ttl } = attributes;

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
                        />
                        <TextControl
                            label={__('From date', 'bailaya')}
                            value={from || ''}
                            onChange={(val) => setAttributes({ from: val })}
                            placeholder="YYYY-MM-DD"
                            help={__('Start of the 7-day window. Defaults to today.', 'bailaya')}
                        />
                        <ToggleControl
                            label={__('Hide location', 'bailaya')}
                            checked={!!hide_location}
                            onChange={(val) => setAttributes({ hide_location: val })}
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
                        {__('Event schedule will render here…', 'bailaya')}
                    </div>
                </div>
            </>
        );
    },
    save: () => null, // dynamic block
});
