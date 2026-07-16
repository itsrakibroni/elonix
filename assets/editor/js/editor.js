(function( $ ) {
	'use strict';
	
	// Inject Custom CSS live in the Elementor Editor seamlessly
	if ( undefined !== window.elementor ) {
		window.elementor.hooks.addFilter( 'editor/style/styleText', function( css, view ) {
			var model = view.getEditModel();
			var customCSS = model.get('settings').get('custom_css');

			if ( customCSS ) {
				css += customCSS.replace( /selector/g, '.elementor-element.elementor-element-' + view.model.get('id') );
			}

			return css;
		} );
	}
})( jQuery );
