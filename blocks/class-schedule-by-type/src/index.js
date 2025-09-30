import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, __experimentalNumberControl as NumberControl } from '@wordpress/components';

wp.blocks.registerBlockType('bailaya/class-schedule-by-type', {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { type_name, from, override_id, locale, cache_ttl } = attributes;
        const blockProps = useBlockProps();

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Filter & Query', 'bailaya')} initialOpen={true}>
                        <TextControl
                            label={__('Type name', 'bailaya')}
                            value={type_name || ''}
                            onChange={(val) => setAttributes({ type_name: val })}
                            placeholder="salsa"
                            help={__('Required. Example: salsa, bachata, tango…', 'bailaya')}
                        />
                        <TextControl
                            label={__('From date (YYYY-MM-DD)', 'bailaya')}
                            value={from || ''}
                            onChange={(val) => setAttributes({ from: val })}
                            placeholder="2025-10-01"
                        />
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
                            placeholder="en, es, fr…"
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
                        { type_name
                            ? __('Filtered class schedule preview will render…', 'bailaya')
                            : __('Set “Type name” to see a preview.', 'bailaya')
                        }
                    </div>
                </div>
            </>
        );
    },
    save: () => null, // dynamic block
});
