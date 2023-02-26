<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

class TaskEventsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $message,
        public string $_subject
    ) {
        $this->subject = $this->_subject;
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.task-events',
        );
    }
}
