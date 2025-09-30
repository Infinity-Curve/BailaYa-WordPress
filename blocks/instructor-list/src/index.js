import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, __experimentalNumberControl as NumberControl } from '@wordpress/components';

wp.blocks.registerBlockType('bailaya/instructor-list', {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { override_id, cache_ttl } = attributes;

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
                        {__('Instructor list preview will render…', 'bailaya')}
                    </div>
                </div>
            </>
        );
    },
    save: () => null, // dynamic block
});
