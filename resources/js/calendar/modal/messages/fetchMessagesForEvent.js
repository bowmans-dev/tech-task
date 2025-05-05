export async function fetchMessagesForEvent(eventId) {
    const messagesContainer = document.getElementById('messages');

    try {
        const response = await fetch(`/events/${eventId}/messages`, {
            headers: {
                'Accept': 'text/vnd.turbo-stream.html',
            },
        });

        if (!response.ok) {
            throw new Error(`Failed to fetch messages for event ID: ${eventId}`);
        }

        const turboStream = await response.text();

        // Clear existing messages and render the Turbo Stream
        messagesContainer.innerHTML = "";
        Turbo.renderStreamMessage(turboStream);
    } catch (error) {
        console.error('Failed to load messages:', error);
    }
}