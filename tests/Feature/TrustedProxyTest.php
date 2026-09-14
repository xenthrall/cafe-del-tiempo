<?php

test('trusts X-Forwarded-Proto from any proxy so requests behind the reverse proxy resolve as secure', function () {
    $this->withServerVariables(['HTTP_X_FORWARDED_PROTO' => 'https'])
        ->get('/up')
        ->assertOk();

    expect(request()->isSecure())->toBeTrue();
});

test('does not treat plain requests as secure', function () {
    $this->get('/up')->assertOk();

    expect(request()->isSecure())->toBeFalse();
});
