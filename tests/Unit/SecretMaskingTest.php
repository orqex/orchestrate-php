<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Tests\Unit;

use Orqex\Orchestrate\ClientConfiguration;
use Orqex\Orchestrate\Exception\InvalidArgumentException;
use Orqex\Orchestrate\OrchestrateClient;
use PHPUnit\Framework\TestCase;

/**
 * A forgotten dd() must not print a live secret key, and a queued or cached
 * payload must not carry one.
 */
final class SecretMaskingTest extends TestCase
{
    private const KEY = 'sk_live_exampleid_examplesecretvalue';

    public function test_dumping_the_configuration_hides_the_key(): void
    {
        $config = new ClientConfiguration(self::KEY);

        ob_start();
        var_dump($config);
        $dump = (string) ob_get_clean();

        $this->assertStringNotContainsString('SECRETVALUE9999', $dump);
        $this->assertStringContainsString('sk_live_***alue', $dump);
    }

    public function test_dumping_the_whole_client_hides_the_key(): void
    {
        $client = new OrchestrateClient(self::KEY);
        $client->paymentIntents();

        ob_start();
        var_dump($client);
        $dump = (string) ob_get_clean();

        $this->assertStringNotContainsString('SECRETVALUE9999', $dump);
    }

    public function test_the_key_stays_readable_for_the_client_itself(): void
    {
        $this->assertSame(self::KEY, (new ClientConfiguration(self::KEY))->apiKey);
    }

    public function test_the_mask_keeps_the_environment_and_last_four(): void
    {
        $this->assertSame('sk_live_***alue', (new ClientConfiguration(self::KEY))->maskedApiKey());
        $this->assertSame('sk_sandbox_***cret', (new ClientConfiguration('sk_sandbox_exampleid_examplesecret'))->maskedApiKey());
    }

    public function test_serialising_the_configuration_is_refused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        serialize(new ClientConfiguration(self::KEY));
    }

    public function test_dumping_the_configuration_hides_private_network_details(): void
    {
        $caBundle = tempnam(sys_get_temp_dir(), 'orqex-ca-');
        $this->assertNotFalse($caBundle);

        try {
            $config = new ClientConfiguration([
                'api_key'    => self::KEY,
                'resolve_ip' => '192.0.2.10',
                'ca_bundle'  => $caBundle,
            ]);

            ob_start();
            var_dump($config);
            $dump = (string) ob_get_clean();

            $this->assertStringNotContainsString('192.0.2.10', $dump);
            $this->assertStringNotContainsString($caBundle, $dump);
        } finally {
            unlink($caBundle);
        }
    }
}
