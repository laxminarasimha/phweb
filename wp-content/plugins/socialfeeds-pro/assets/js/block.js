(function (blocks, element, blockEditor, components, serverSideRender) {
    const el = element.createElement;
    const { InspectorControls } = blockEditor;
    const { PanelBody, TextControl } = components;
    const ServerSideRender = serverSideRender;

    function registerSocialFeedBlock(slug, title, icon, helpText = '') {
        const feeds = (window.socialfeeds_blocks_data && window.socialfeeds_blocks_data[slug + '_feeds']) || [];
        const feedOptions = [
            { label: 'Select Feed', value: '' },
            ...feeds.map(feed => ({
                label: feed.name || `Feed #${feed.id}`,
                value: feed.id
            }))
        ];

        blocks.registerBlockType(`socialfeeds/${slug}`, {
            title: title,
            icon: icon,
            supports: {
                align: ['wide', 'full']
            },
            attributes: {
                id: { type: 'string', default: '' },
                width: { type: 'string', default: '' },
                align: { type: 'string', default: '' }
            },
            edit: function (props) {
                const { attributes, setAttributes, className } = props;

                // Inspector Sidebar Controls
                const inspectorControls = el(InspectorControls, null,
                    el(PanelBody, { title: `${title} Settings` },
                        el(components.SelectControl, {
                            label: 'Select Feed',
                            value: attributes.id,
                            options: feedOptions,
                            onChange: (value) => setAttributes({ id: value })
                        }),
                        el(components.SelectControl, {
                            label: 'Width',
                            value: attributes.width || '',
                            options: [
                                { label: 'None', value: 'none' },
                                { label: '100%', value: '100%' },
                                { label: '90%', value: '90%' },
                                { label: '80%', value: '80%' },
                                { label: '70%', value: '70%' },
                                { label: '60%', value: '60%' },
                                { label: '50%', value: '50%' },
                            ],
                            onChange: (value) => setAttributes({ width: value })
                        }),
                        el(components.SelectControl, {
                            label: 'Alignment',
                            value: attributes.align || '',
                            options: [
                                { label: 'Default', value: '' },
                                { label: 'Left', value: 'left' },
                                { label: 'Center', value: 'center' },
                                { label: 'Right', value: 'right' },
                                { label: 'Wide width', value: 'wide' },
                                { label: 'Full width', value: 'full' },
                            ],
                            onChange: (value) => setAttributes({ align: value })
                        })
                    )
                );

                // If no feed is selected, show selection UI in the block area
                if (!attributes.id) {
                    return el('div', { className: className + ' socialfeeds-block-setup' },
                        inspectorControls,
                        el(components.Placeholder, {
                            icon: icon,
                            label: title,
                            instructions: 'Choose a saved feed to display from the list below:'
                        },
                            el('div', {
                                className: 'socialfeeds-placeholder-list',
                                style: { width: '100%', maxWidth: '400px', padding: '10px 0' }
                            },
                                feeds.length > 0 ? (
                                    feeds.map(feed => el(components.Button, {
                                        variant: 'secondary',
                                        isSmall: false,
                                        style: {
                                            marginBottom: '8px',
                                            width: '100%',
                                            justifyContent: 'center',
                                            height: '40px'
                                        },
                                        onClick: () => setAttributes({ id: feed.id.toString() })
                                    }, feed.name || `Feed #${feed.id}`))
                                ) : (
                                    el('div', { style: { textAlign: 'center', color: '#666' } },
                                        'No saved feeds found. Please create a feed in the plugin dashboard first.'
                                    )
                                )
                            )
                        )
                    );
                }

                return el('div', { className: className },
                    inspectorControls,
                    el('div', { className: 'socialfeeds-block-editor-preview' },
                        el(ServerSideRender, { block: `socialfeeds/${slug}`, attributes: attributes })
                    )
                );
            },
            save: function () {
                return null;
            }
        });
    }


    registerSocialFeedBlock('youtube', 'YouTube Feed', 'youtube');
    registerSocialFeedBlock('instagram', 'Instagram Feed', 'instagram', 'Enter the Instagram feed ID (required for display)');
    registerSocialFeedBlock('facebook', 'Facebook Feed', 'facebook');
	registerSocialFeedBlock('google', 'google Feed','google', 'Enter google feed ID');
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.serverSideRender);