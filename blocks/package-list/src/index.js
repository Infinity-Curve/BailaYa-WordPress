import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, __experimentalNumberControl as NumberControl } from '@wordpress/components';

wp.blocks.registerBlockType('bailaya/package-list', {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { override_id, locale, hide_validity, cache_ttl, buy_base_url } = attributes;

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
                            label={__('Hide validity period', 'bailaya')}
                            checked={!!hide_validity}
                            onChange={(val) => setAttributes({ hide_validity: val })}
                            help={__('Hide the "Valid for N months" line on each package.', 'bailaya')}
                        />
                        <TextControl
                            label={__('Buy Base URL', 'bailaya')}
                            value={buy_base_url || ''}
                            onChange={(val) => setAttributes({ buy_base_url: val })}
                            placeholder="https://www.bailaya.com/packages/"
                            help={__('The package ID will be appended to this URL.', 'bailaya')}
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
                        {__('Class packages will render here…', 'bailaya')}
                    </div>
                </div>
            </>
        );
    },
    save: () => null, // dynamic block
});
