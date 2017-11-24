(function() {
	if (!window.nl4wp) {
		window.nl4wp = {
			listeners: [],
			forms    : {
				on: function (event, callback) {
					window.nl4wp.listeners.push({
						event   : event,
						callback: callback
					});
				}
			}
		}
	}
})();
