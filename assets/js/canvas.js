(function() {
	function makeParentsFluid() {
		var canvas = document.getElementById('es-archive-primary');
		var fluidClass = 'es-archive-parent-fluid';
		if (!canvas) {
			canvas = document.getElementById('es-search-primary');
			fluidClass = 'es-search-parent-fluid';
		}
		if (!canvas) {
			canvas = document.getElementById('es-single-primary');
			fluidClass = 'es-single-parent-fluid';
		}
		if (canvas) {
			var parent = canvas.parentElement;
			while (parent && parent.tagName !== 'BODY' && parent.tagName !== 'HTML') {
				if (parent.id === 'page' || parent.classList.contains('site') || parent.classList.contains('page-wrapper')) {
					break;
				}
				parent.classList.add(fluidClass);
				parent = parent.parentElement;
			}
		}
	}
	makeParentsFluid();
	document.addEventListener('DOMContentLoaded', makeParentsFluid);
})();
