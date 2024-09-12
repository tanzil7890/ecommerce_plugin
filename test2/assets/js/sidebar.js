( function( wp ) {
    var registerPlugin = wp.plugins.registerPlugin;
    var PluginSidebar = wp.editPost.PluginSidebar;
    var el = wp.element.createElement;

    registerPlugin( 'my-collection-sidebar', {
        render: function() {
            return el(
                PluginSidebar,
                {
                    name: 'my-collection-sidebar',
                    title: 'My Collections',
                },
                el(
                    'div',
                    { className: 'my-collection-sidebar-content' },
                    el(
                        'a',
                        { href: ajaxurl + '?action=open_collection_dashboard', className: 'button' },
                        'Open Collections Dashboard'
                    )
                )
            );
        },
    } );
} )( window.wp );