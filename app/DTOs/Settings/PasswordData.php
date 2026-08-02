<?php

namespace App\DTOs\Settings;

final readonly class PasswordData
{
    public function __construct(
        #[\SensitiveParameter]
        public string $password,
    ) {}

    public static function fromArray(#[\SensitiveParameter] array $data): self
    {
        return new self(password: $data['password']);
    }
}
