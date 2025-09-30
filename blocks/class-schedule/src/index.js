import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, __experimentalNumberControl as NumberControl } from '@wordpress/components';

wp.blocks.registerBlockType('bailaya/class-schedule', {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { from, override_id, locale, cache_ttl } = attributes;

        const blockProps = useBlockProps();

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Query', 'bailaya')} initialOpen={true}>
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
                    {/* Dynamic blocks render server-side in the editor; this is a placeholder while loading */}
                    <div style={{ opacity: 0.6, fontStyle: 'italic' }}>
                        {__('Class schedule preview will render here…', 'bailaya')}
                    </div>
                </div>
            </>
        );
    },
    save: () => null, // Dynamic block – rendered in PHP
});
