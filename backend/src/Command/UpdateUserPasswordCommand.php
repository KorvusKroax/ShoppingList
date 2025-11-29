<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:update-user-password',
    description: 'Updates the password for a specific user in the database.',
)]
class UpdateUserPasswordCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    // A szolgáltatásokat (EntityManager, PasswordHasher) a konstruktoron keresztül injektáljuk
    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // --- 1. Felhasználó Adatainak Beállítása ---
        $userEmail = 'test@example.com'; // A régi felhasználó e-mail címe
        $newPlainPassword = 'tesztjelszo'; // A beállítani kívánt új jelszó

        // --- 2. Felhasználó Betöltése az Adatbázisból ---
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userEmail]);

        if (!$user) {
            $io->error(sprintf('User with email "%s" not found.', $userEmail));
            return Command::FAILURE;
        }

        // --- 3. Jelszó Hashelése ---
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $newPlainPassword
        );

        // --- 4. Jelszó Frissítése és Mentés ---
        $user->setPassword($hashedPassword);
        $this->entityManager->flush();

        $io->success(sprintf('Password successfully updated for user: %s', $userEmail));

        return Command::SUCCESS;
    }
}
