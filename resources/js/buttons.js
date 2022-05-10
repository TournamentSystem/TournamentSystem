$(function() {
	$('.btn[data-ts-btn-loading]').on('click', function() {
		const TYPES = ['border', 'grow'];
		
		let $this = $(this);
		let type = $this.data('ts-btn-loading');
		
		if(!TYPES.includes(type)) {
			type = TYPES[0];
		}
		
		let $spinner = $(`<span class="spinner-${type} spinner-${type}-sm me-1" role="status" aria-hidden="true"></span>`);
		
		$this.prop('disabled', true);
		$this.prepend($spinner);
	});
});
