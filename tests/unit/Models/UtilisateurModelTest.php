<?php

use App\Models\UtilisateurModel;
use CodeIgniter\Test\CIUnitTestCase;

final class UtilisateurModelTest extends CIUnitTestCase
{
    public function testHashPasswordCallbackHashesPlainTextPassword(): void
    {
        $model = new UtilisateurModel();
        $reflection = new \ReflectionMethod($model, 'hashPassword');
        $reflection->setAccessible(true);

        $input = [
            'data' => [
                'mot_de_passe' => 'password123',
            ],
        ];

        $result = $reflection->invoke($model, $input);

        $this->assertArrayHasKey('mot_de_passe', $result['data']);
        $this->assertNotSame('password123', $result['data']['mot_de_passe']);
        $this->assertTrue(password_verify('password123', $result['data']['mot_de_passe']));
    }

    public function testHashPasswordCallbackSkipsAlreadyHashedPassword(): void
    {
        $model = new UtilisateurModel();
        $reflection = new \ReflectionMethod($model, 'hashPassword');
        $reflection->setAccessible(true);

        $hashed = password_hash('password123', PASSWORD_DEFAULT);
        $input = [
            'data' => [
                'mot_de_passe' => $hashed,
            ],
        ];

        $result = $reflection->invoke($model, $input);

        $this->assertSame($hashed, $result['data']['mot_de_passe']);
    }
}
