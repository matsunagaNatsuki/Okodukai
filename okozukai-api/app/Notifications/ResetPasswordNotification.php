<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $token
    ){}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('【おこづかい】パスワード再設定のお知らせ')
            ->greeting('こんにちは！')
            ->line('パスワード再設定のリクエストを受け付けました。')
            ->line('下のボタンを押して、新しいパスワードを設定してください。')
            ->action('パスワードを再設定する', $url)
            ->line('このパスワード再設定リンクの有効期限は60分です。')
            ->line('パスワード再設定を依頼していない場合は、このメールを無視してください。');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
