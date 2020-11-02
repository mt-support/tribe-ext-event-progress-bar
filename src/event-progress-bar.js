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
	for( let i = 0; i < events.length; i++ ) {
		var startDate = new Date( events[ i ].children[ 0 ].dataset.startDate ).getTime();
		var endDate = new Date( events[ i ].children[ 0 ].dataset.endDate ).getTime();

		var fullTime = endDate - startDate;
		var timeLeft = endDate - (now + offset);
		var percent = (fullTime - timeLeft) / fullTime * 100;

		// Time calculations for days, hours, minutes and seconds
		var days = Math.floor( timeLeft / (1000 * 60 * 60 * 24) );
		var hours = Math.floor( (timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60) );
		var minutes = Math.floor( (timeLeft % (1000 * 60 * 60)) / (1000 * 60) );
		var seconds = Math.floor( (timeLeft % (1000 * 60)) / 1000 );

		// Output the result in an element with id="demo"
		var timeString = '';
		if ( days > 0 ) timeString += days + 'd ';
		if ( hours < 10 ) hours = '0' + hours;
		if ( minutes < 10 ) minutes = '0' + minutes;
		if ( seconds < 10 ) seconds = '0' + seconds;
		timeString += hours + ":" + minutes + ":" + seconds;

		events[ i ].children[ 2 ].children[ 0 ].innerHTML = timeString;
		events[ i ].children[ 1 ].children[ 0 ].style.width = 'calc(' + percent + '% - 9px)';

		// If the count down is over, write some text
		if ( timeLeft < 0 ) {
			events[ i ].children[ 0 ].innerHTML = "Event";
			events[ i ].children[ 2 ].children[ 0 ].innerHTML = "is over.";
			events[ i ].classList.remove( "progress-bar-container__on" );
		}
	}
}
