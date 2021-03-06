<?php

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class NotificationSlackChannelTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    /**
     * @param  \Illuminate\Notifications\Notification  $notification
     * @param  array  $payload
     */
    protected function validatePayload($notification, $payload)
    {
        $notifiable = new NotificationSlackChannelTestNotifiable;

        $channel = new Illuminate\Notifications\Channels\SlackWebhookChannel(
            $http = Mockery::mock('GuzzleHttp\Client')
        );

        $http->shouldReceive('post')->with('url', $payload);

        $channel->send($notifiable, $notification);
    }

    public function testCorrectPayloadIsSentToSlack()
    {
        $this->validatePayload(
            new NotificationSlackChannelTestNotification,
            [
                'json' => [
                    'username' => 'Ghostbot',
                    'icon_emoji' => ':ghost:',
                    'channel' => '#ghost-talk',
                    'text' => 'Content',
                    'attachments' => [
                        [
                            'title' => 'Laravel',
                            'title_link' => 'https://laravel.com',
                            'text' => 'Attachment Content',
                            'fields' => [
                                [
                                    'title' => 'Project',
                                    'value' => 'Laravel',
                                    'short' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    public function testCorrectPayloadWithoutOptionalFieldsIsSentToSlack()
    {
        $this->validatePayload(
            new NotificationSlackChannelWithoutOptionalFieldsTestNotification,
            [
                'json' => [
                    'text' => 'Content',
                    'attachments' => [
                        [
                            'title' => 'Laravel',
                            'title_link' => 'https://laravel.com',
                            'text' => 'Attachment Content',
                            'fields' => [
                                [
                                    'title' => 'Project',
                                    'value' => 'Laravel',
                                    'short' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}

class NotificationSlackChannelTestNotifiable
{
    use Illuminate\Notifications\Notifiable;

    public function routeNotificationForSlack()
    {
        return 'url';
    }
}

class NotificationSlackChannelTestNotification extends Notification
{
    public function toSlack($notifiable)
    {
        return (new SlackMessage)
                    ->from('Ghostbot', ':ghost:')
                    ->to('#ghost-talk')
                    ->content('Content')
                    ->attachment(function ($attachment) {
                        $attachment->title('Laravel', 'https://laravel.com')
                                   ->content('Attachment Content')
                                   ->fields([
                                        'Project' => 'Laravel',
                                    ]);
                    });
    }
}

class NotificationSlackChannelWithoutOptionalFieldsTestNotification extends Notification
{
    public function toSlack($notifiable)
    {
        return (new SlackMessage)
                    ->content('Content')
                    ->attachment(function ($attachment) {
                        $attachment->title('Laravel', 'https://laravel.com')
                                   ->content('Attachment Content')
                                   ->fields([
                                        'Project' => 'Laravel',
                                    ]);
                    });
    }
}
