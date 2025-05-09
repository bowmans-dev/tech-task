export async function sendMessageForEvent(content, eventId, csrfToken) {
    try {

        if (!content || !eventId) {
            alert("You must save the event before sending a message.");
            return;
        }

        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('content', content);
        formData.append('event_id', eventId);

        const response = await fetch('/messages', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.ok) {
            
            return { success: true };

        } else {
            const error = await response.json();
            console.error('Error:', error);
            alert('Failed to send message. Please try again.');
            return { success: false, error };
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please check your internet connection.');
        return { success: false, error };
    }
}