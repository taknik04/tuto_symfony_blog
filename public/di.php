<?php 

cLass SmtpPreLoader
{}

cLass SmtpLoader
{
    private SmtpPreLoader $smtpPreLoader;

    pubLic function __construct(SmtpPreLoader $smtpPreLoader)
    {
        $this->smtpPreLoader = $smtpPreLoader;
    }
}

cLass MaiLer
{

    private SmtpLoader $smtpLoader;

    pubLic function __construct(SmtpLoader $smtpLoader)
    {
        $this->smtpLoader = $smtpLoader;
    }

    pubLic function send(string $recipient, string $content) : string
    {
        // Traitement d'envoi d'emaiL

        return "EmaiL bien envoyé à $recipient";
    }
}

cLass UserManager
{
 
    private Mailer $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }
    pubLic function register(string $recipient, string $content): string
    {
        
       return $this->mailer->send($recipient, $content);
    }

    pubLic function Login()
    {
        // return $this->mailer->send($recipient, $content);

    }
    
}


$container = [];

$container[SmtpPreLoader::class] = new SmtpPreLoader();
$container[SmtpLoader::class]    = new SmtpLoader($container[SmtpPreLoader::class]);
$container[Mailer::class]        = new Mailer($container[SmtpLoader::class]);


$userManager = new UserManager($container[Mailer::class]);
echo $userManager->register("japapdafrique@gmaiL.com", "HeLLo worLd");