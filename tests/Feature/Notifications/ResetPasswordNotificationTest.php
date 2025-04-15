<?php

namespace Tests\Feature\Notifications;

use App\Models\User; // Adjust the namespace based on your User model
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResetPasswordNotificationTest extends TestCase
{
    /**
     * Test the notification sends the reset password email.
     */
    public function test_reset_password_notification_sends_email()
    {
        // Fake notifications
        Notification::fake();

        // Create a test user
        $user = User::factory()->create();

        // Simulate sending the notification
        $token = 'sample-token';
        $user->notify(new ResetPasswordNotification($token));

        // Assert that the notification was sent to the user
        Notification::assertSentTo(
            [$user],
            ResetPasswordNotification::class,
            function ($notification, $channels) use ($token) {
                $this->assertEquals($notification->token, $token);

                return true; // Indicates the notification was sent
            }
        );
    }

    /**
     * Test the reset password email contains the correct reset URL.
     */
    public function test_reset_password_email_contains_correct_url()
    {
        // Set up the notification
        $token = 'sample-token';
        $user = User::factory()->create(); // Assumes User factory is available
        $notification = new ResetPasswordNotification($token);

        // Generate the mail message
        $mailMessage = $notification->toMail($user);

        // Extract the reset URL
        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ], false));

        // Assert the email contains the reset URL
        $this->assertStringContainsString($url, $mailMessage->render());
    }

    /**
     * Test using a custom mail callback.
     */
    public function test_reset_password_notification_uses_custom_mail_callback()
    {
        // Set a custom callback for building the mail message
        ResetPasswordNotification::toMailUsing(function ($notifiable, $token) {
            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Custom Subject')
                ->line('This is a custom reset password email.')
                ->action('Reset Password', url('/custom-reset-url/'.$token));
        });

        // Create a test user
        $user = User::factory()->create();

        // Generate the notification
        $token = 'sample-token';
        $notification = new ResetPasswordNotification($token);
        $mailMessage = $notification->toMail($user);

        // Assert that the custom subject, content, and URL are used
        $this->assertEquals('Custom Subject', $mailMessage->subject);
        $this->assertStringContainsString('This is a custom reset password email.', $mailMessage->render());
        $this->assertStringContainsString('/custom-reset-url/'.$token, $mailMessage->render());
    }
}
