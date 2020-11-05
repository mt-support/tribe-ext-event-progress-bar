// Update the count down every 1 second
var x = setInterval( doProgress, 1000 );

function doProgress() {

	/**
	 * Get all the active progress bars from the page.
	 *
	 * Child elements:
	 * 0 - progress-bar-container__live
	 * 1 - progress-bar-container__background
	 * 2 - progress-bar-container__timeleft
	 */
	var events = document.getElementsByClassName( 'progress-bar-container__on' );

	// Bail if there are no live events.
	if ( events.length <= 0 ) {
		//clearInterval( x );
		return;
	}

	// Get today's date and time and the offset from UTC.
	var d = new Date();
	var now = d.getTime();
	var tzOffset = d.getTimezoneOffset();
	var offset = tzOffset * 60 * 1000;

	// Go through all the events that are running at the moment.
	Array.prototype.forEach.call(events, function( event ) {
		var startDate = new Date( event.querySelector( '.progress-bar-container__live' ).dataset.startDate ).getTime();
		var endDate = new Date( event.querySelector( '.progress-bar-container__live' ).dataset.endDate ).getTime();

		var fullTime = endDate - startDate;
		var timeLeft = endDate - (now + offset);
		var percent = (fullTime - timeLeft) / fullTime * 100;

		if ( timeLeft < 0 ) {
			// If the countdown is over change the labels.
			event.querySelector( '.progress-bar-container__live-text' ).classList.add('tribe-common-a11y-hidden');
			event.querySelector( '.progress-bar-container__live-text--over' ).classList.remove('tribe-common-a11y-hidden');
			event.querySelector( '.progress-bar-container__timeleft-time' ).classList.add('tribe-common-a11y-hidden');
			event.querySelector( '.progress-bar-container__timeleft-string' ).classList.add('tribe-common-a11y-hidden');
			event.querySelector( '.progress-bar-container__timeleft--over' ).classList.remove('tribe-common-a11y-hidden');
			event.classList.remove( "progress-bar-container__on" );
		} else {
			// Time calculations for days, hours, minutes and seconds
			var second = 1000;
			var minute = second * 60; // (1000 * 60)
			var hour = minute * 60;   // (1000 * 60 * 60)
			var day = hour * 24;      // (1000 * 60 * 60 * 24)

			var days = Math.floor( timeLeft / day );
			var hours = Math.floor( (timeLeft % day) / hour ).toString(10);
			var minutes = Math.floor( (timeLeft % hour) / minute ).toString(10);
			var seconds = Math.floor( (timeLeft % minute) / second ).toString(10);

			// Output the result in an element
			var timeString = '';
			if ( days > 0 ) { timeString += days + 'd '; }

			timeString += hours.padStart( 2, 0 ) + ":" + minutes.padStart( 2, 0 ) + ":" + seconds.padStart( 2, 0 );

			event.querySelector( '.progress-bar-container__timeleft .progress-bar-container__timeleft-time' ).innerHTML = timeString;
			event.querySelector( '.progress-bar-container__background .progress-bar-container__progressbar').style.width = 'calc(' + percent + '% - 9px)';
		}
	});
}
