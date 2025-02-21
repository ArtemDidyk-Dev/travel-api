<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommentPublished extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $link
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Comment Published');
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.comment.published',
            with: [
                'url' => $this->link,
            ],
        );
    }
}
