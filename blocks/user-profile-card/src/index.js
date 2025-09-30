import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, __experimentalNumberControl as NumberControl } from '@wordpress/components';

wp.blocks.registerBlockType('bailaya/user-profile-card', {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { user_id, locale, cache_ttl, occupation_label, experience_label } = attributes;
        const blockProps = useBlockProps();

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('User', 'bailaya')} initialOpen={true}>
                        <TextControl
                            label={__('User ID', 'bailaya')}
                            value={user_id || ''}
                            onChange={(v) => setAttributes({ user_id: v })}
                            placeholder="user-456"
                            help={__('Required', 'bailaya')}
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
                            label={__('Occupation label', 'bailaya')}
                            value={occupation_label || ''}
                            onChange={(v) => setAttributes({ occupation_label: v })}
                            placeholder=""
                        />
                        <TextControl
                            label={__('Experience label', 'bailaya')}
                            value={experience_label || ''}
                            onChange={(v) => setAttributes({ experience_label: v })}
                            placeholder=""
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    <em style={{ opacity: .6 }}>
                        { user_id
                            ? __('User profile preview will render…', 'bailaya')
                            : __('Set “User ID” to see a preview.', 'bailaya') }
                    </em>
                </div>
            </>
        );
    },
    save: () => null, // dynamic block
});
