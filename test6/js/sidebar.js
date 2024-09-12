(function(wp) {
    var registerPlugin = wp.plugins.registerPlugin;
    var PluginSidebar = wp.editPost.PluginSidebar;
    var el = wp.element.createElement;

    registerPlugin('pcm-sidebar', {
        render: function() {
            return el(
                PluginSidebar,
                {
                    name: 'pcm-sidebar',
                    title: 'Product Collections'
                },
                el(
                    'div',
                    { className: 'pcm-sidebar-content' },
                    el(
                        'a',
                        {
                            href: '/wp-admin/admin.php?page=pcm-dashboard',
                            className: 'button button-primary'
                        },
                        'Open Dashboard'
                    )
                )
            );
        }
    });
})(window.wp);