<?php
// src/Service/MailService.php
namespace App\Service;

use App\Repository\MailRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

readonly class MailService
{

    public function __construct(
        private MailerInterface $mailer,
        private MailRepository $mailRepository
    )
    {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmail(string $to, string $mailType, $strToReplace = []): void
    {

        // Récupération de l'entité Mail en fonction du typeMail
        $mail = $this->mailRepository->findOneBy(['typeMail' => $mailType]);

        if (!$mail) {
            throw new \InvalidArgumentException("Aucun mail trouvé pour le type : $mailType");
        }

        $subject = $mail->getSubject();
        $content = $mail->getContent();
        //on dé htmlspecialchar
        $subject = htmlspecialchars_decode($subject, ENT_QUOTES);
        $content = htmlspecialchars_decode($content, ENT_QUOTES);

        foreach ($strToReplace as $key => $value)
        {
            $subject = str_replace('{'.$key.'}', $value, $subject);
            $content = str_replace('{'.$key.'}', $value, $content);
        }

        $email = (new Email())
            ->from('hello@demomailtrap.co')
            ->to($to)
            ->subject($subject)
            ->text($content)
            ->html($content);

        $this->mailer->send($email);

    }
}
