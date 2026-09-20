<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class MessengerConfigTest extends TestCase
{
    public function testMailerMessagesAreSentSynchronously(): void
    {
        $config = Yaml::parseFile(__DIR__ . '/../config/packages/messenger.yaml');

        $routing = $config['framework']['messenger']['routing'] ?? [];

        $this->assertSame(
            'sync',
            $routing['Symfony\\Component\\Mailer\\Messenger\\SendEmailMessage'] ?? null,
            'Le mail de vérification doit être envoyé immédiatement, sans passer par une file asynchrone.'
        );
    }
}
