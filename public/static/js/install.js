let deferredPrompt;

window.addEventListener('beforeinstallprompt', (event) => {
	// Prevent Chrome 67 and earlier from automatically showing the prompt
	event.preventDefault();

	// Stash the event so it can be triggered later
	deferredPrompt = event;

	// Show a custom install button or similar, and call prompt() when clicked
	// installButton.style.display = 'block';
});

const installButton = document.getElementById('installButton');

installButton.addEventListener('click', () => {
	if (deferredPrompt) {
		// Show the prompt
		deferredPrompt.prompt();

		// Wait for the user to respond to the prompt
		deferredPrompt.userChoice.then((choiceResult) => {
			if (choiceResult.outcome === 'accepted') {
				console.log('User accepted the install prompt');
			} else {
				console.log('User dismissed the install prompt');
			}

			// Reset the deferred prompt variable
			deferredPrompt = null;
		});
	}
});