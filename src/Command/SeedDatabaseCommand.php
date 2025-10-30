<?php

namespace App\Command;

use App\Entity\Cart;
use App\Entity\Category;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\Plant;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed-database',
    description: 'Seeds the database with a complete set of initial data.',
)]
class SeedDatabaseCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Seeding Database...');

        // Vider les tables 
        $this->truncateAllTables($io);

        // 2. Create Categories
        $io->section('Creating Categories...');
        $categories = [];
        $categoryNames = ['Plantes d\'intérieur',
            'Plantes d\'extérieur',
            'Fleurs',
            'Arbres',
            'Arbustes',
            'Plantes grasses',
            'Plantes aromatiques'];
        foreach ($categoryNames as $name) {
            $category = new Category();
            $category->setName($name);
            $this->entityManager->persist($category);
            $categories[] = $category;
        }
        $this->entityManager->flush();
        $io->info(count($categories) . ' categories created.');

        // Créer Admin et user
        $io->section('Creating Users...');
        $users = [];
        $admin = new User();
        $admin->setEmail('admin@plantoshop.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('User');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'adminpass'));
        $this->entityManager->persist($admin);
        $users[] = $admin;

        $user = new User();
        $user->setEmail('user@plantoshop.com');
        $user->setFirstName('Regular');
        $user->setLastName('User');
        $user->setAddress('123 Green St, Plant City');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'userpass'));
        $this->entityManager->persist($user);
        $users[] = $user;
        $this->entityManager->flush();
        $io->info(count($users) . ' users created.');

        // Créer des plantes
        $io->section('Creating Plants...');
        $plants = [];
        for ($i = 1; $i <= 20; $i++) {
            $plant = new Plant();
            $plant->setName('Plante ' . $i);
            $plant->setDescription('Ceci est la description de la plante ' . $i . '.');
            $plant->setPrice(mt_rand(10, 100));
            $plant->setCategory($categories[array_rand($categories)]);
            $plant->setOwner($admin); // All plants are created by the admin
            $this->entityManager->persist($plant);
            $plants[] = $plant;
        }
        $this->entityManager->flush();
        $io->info(count($plants) . ' plants created.');

        // Créer des commandes
        $io->section('Creating Carts, Orders, and OrderDetails...');
        foreach ($users as $currentUser) {
            // Créer un panier pour chaque utilisateur
            $cart = new Cart();
            $cart->setOwner($currentUser);

            // Ajouter 3 plantes au panier aléatoirement
            $plantsInCart = (array)array_rand($plants, 3);
            $totalPrice = 0;
            $plantSummary = [];
            foreach ($plantsInCart as $plantIndex) {
                $plantToAdd = $plants[$plantIndex];
                $cart->addPlant($plantToAdd);
                $totalPrice += $plantToAdd->getPrice();
                $plantSummary[] = ['name' => $plantToAdd->getName(), 'price' => $plantToAdd->getPrice()];
            }
            $this->entityManager->persist($cart);

            // Créer une commande pour chaque panier
            $order = new Order();
            $order->setClient($currentUser);
            $order->setCart($cart);
            foreach ($cart->getPlants() as $plant) {
                $order->addPlant($plant);
            }
            $this->entityManager->persist($order);

            // Créer les détails de la commande pour chaque commande
            $orderDetails = new OrderDetails();
            $orderDetails->setOrder($order);
            $orderDetails->setClientFirstName($currentUser->getFirstName());
            $orderDetails->setClientLastName($currentUser->getLastName());
            $orderDetails->setClientEmail($currentUser->getEmail());
            $orderDetails->setClientAddress($currentUser->getAddress());
            $orderDetails->setClientPhoneNumber($currentUser->getPhoneNumber());
            $orderDetails->setOrderDate(new \DateTimeImmutable());
            $orderDetails->setTotalPrice((string)$totalPrice);
            $orderDetails->setPlantSummary($plantSummary);
            $this->entityManager->persist($orderDetails);

            $io->text('Created cart, order, and details for ' . $currentUser->getEmail());
        }

        $this->entityManager->flush();
        $io->info('Carts, Orders, and OrderDetails created.');

        $io->success('Database seeding complete!');

        return Command::SUCCESS;
    }

    private function truncateAllTables(SymfonyStyle $io): void
    {
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        
        // Vide et réinitialise les tables de la base de données pour un nouveau départ.
        $io->info('Truncating all tables (PostgreSQL method)...');
        $connection->executeStatement('TRUNCATE TABLE "order_details",
         "order_plant", "order", "cart_plant", "cart", "plant", "category",
         "user" RESTART IDENTITY CASCADE');
    }
}
