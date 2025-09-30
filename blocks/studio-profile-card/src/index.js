import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, __experimentalNumberControl as NumberControl } from '@wordpress/components';

wp.blocks.registerBlockType('bailaya/studio-profile-card', {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { override_id, locale, cache_ttl, address_label, business_hours_label } = attributes;
        const blockProps = useBlockProps();

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Settings', 'bailaya')} initialOpen={true}>
                        <TextControl
                            label={__('Override Studio ID', 'bailaya')}
                            value={override_id || ''}
                            onChange={(v) => setAttributes({ override_id: v })}
                            placeholder="studio-123"
                        />
                        <TextControl
                            label={__('Locale', 'bailaya')}
                            value={locale || ''}
                            onChange={(v) => setAttributes({ locale: v })}
                            placeholder="en, es, fr..."
                        />
                        <NumberControl
                            label={__('Cache TTL (seconds)', 'bailaya')}
                            min={0}
                            value={Number(cache_ttl || 0)}
                            onChange={(v) => setAttributes({ cache_ttl: Number(v || 0) })}
                        />
                    </PanelBody>
                    <PanelBody title={__('Labels', 'bailaya')} initialOpen={false}>
                        <TextControl
                            label={__('Address label', 'bailaya')}
                            value={address_label || ''}
                            onChange={(v) => setAttributes({ address_label: v })}
                            placeholder=""
                        />
                        <TextControl
                            label={__('Business hours label', 'bailaya')}
                            help={__('Defaults to “Business Hours” (or “Horario Comercial” for es).', 'bailaya')}
                            value={business_hours_label || ''}
                            onChange={(v) => setAttributes({ business_hours_label: v })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    <em style={{ opacity: .6 }}>
                        {__('Studio profile preview will render…', 'bailaya')}
                    </em>
                </div>
            </>
        );
    },
    save: () => null, // dynamic block
});
