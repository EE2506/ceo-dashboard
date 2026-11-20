<?php

namespace Tests\Unit;

use App\Rules\ReferenceUrl;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_reference_links_reject_credentials_and_allow_regular_https_links(): void
    {
        $rule = new ReferenceUrl();

        $rule->validate('link', 'https://example.com/design?node-id=22', fn () => $this->fail('Valid link rejected.'));

        foreach ([
            'javascript:alert(1)',
            'https://demo:demo@example.com',
            'https://example.com?%61ccess_token=placeholder',
            'https://example.com#oauth?code=placeholder',
            'https://example.com?X-Amz-Signature=placeholder',
        ] as $url) {
            $rejected = false;
            $rule->validate('link', $url, function () use (&$rejected): void {
                $rejected = true;
            });

            $this->assertTrue($rejected, $url);
        }
    }
}
