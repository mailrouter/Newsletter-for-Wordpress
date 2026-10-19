(function() {
	window.nl4wp = window.nl4wp || {
		listeners: [],
		forms: {
			on: function(evt, cb) {
				window.nl4wp.listeners.push(
					{
						event   : evt,
						callback: cb
					}
				);
			}
		}
	}
})();
