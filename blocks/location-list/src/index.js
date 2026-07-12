import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, __experimentalNumberControl as NumberControl } from '@wordpress/components';

wp.blocks.registerBlockType('bailaya/location-list', {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { override_id, locale, hide_primary_badge, hide_directions, cache_ttl } = attributes;

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
                        <ToggleControl
                            label={__('Hide "Primary" badge', 'bailaya')}
                            checked={!!hide_primary_badge}
                            onChange={(val) => setAttributes({ hide_primary_badge: val })}
                            help={__('Hide the badge marking the studio\'s primary location.', 'bailaya')}
                        />
                        <ToggleControl
                            label={__('Hide directions link', 'bailaya')}
                            checked={!!hide_directions}
                            onChange={(val) => setAttributes({ hide_directions: val })}
                            help={__('Hide the link that opens the location in Google Maps.', 'bailaya')}
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
                        {__('Studio locations will render here…', 'bailaya')}
                    </div>
                </div>
            </>
        );
    },
    save: () => null, // dynamic block
});
