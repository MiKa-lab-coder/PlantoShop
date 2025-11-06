<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\Plant;
use App\Entity\User;
use Doctrine\DBAL\Exception;
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

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seeding Database...');

        $this->truncateAllTables($io);

        $io->section('Creating Categories...');
        // Données des catégories pour tests
        $categoryNames = [
            'Plantes d\'intérieur' => 'int',
            'Plantes d\'extérieur' => 'ext',
            'Fleurs' => 'fleur',
            'Plantes grasses' => 'grass',
            'Plantes aromatiques' => 'arom',
        ];
        $categories = [];
        foreach ($categoryNames as $name => $prefix) {
            $category = new Category();
            $category->setName($name);
            $this->entityManager->persist($category);
            $categories[$prefix] = $category;
        }
        $this->entityManager->flush();
        $io->info(count($categories) . ' categories created.');

        $io->section('Creating Users...');
        // Données des utilisateurs pour tests
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

        $io->section('Creating Plants...');
        // Données des plantes pour tests
        $plantData = [
            ['name' => 'Monstera Deliciosa', 'cat' => 'int', 'img' => 'int1.png', 'price' => 25],
            ['name' => 'Ficus Lyrata', 'cat' => 'int', 'img' => 'int2.png', 'price' => 35],
            ['name' => 'Sansevieria Trifasciata', 'cat' => 'int', 'img' => 'int3.png', 'price' => 18],
            ['name' => 'Lavande Officinale', 'cat' => 'ext', 'img' => 'ext1.jpg', 'price' => 12],
            ['name' => 'Rosier Grimpant', 'cat' => 'ext', 'img' => 'ext2.jpg', 'price' => 30],
            ['name' => 'Hortensia Macrophylla', 'cat' => 'ext', 'img' => 'ext3.jpg', 'price' => 22],
            ['name' => 'Tulipe Darwin', 'cat' => 'fleur', 'img' => 'fleur1.png', 'price' => 5],
            ['name' => 'Pivoine Arbustive', 'cat' => 'fleur', 'img' => 'fleur2.png', 'price' => 40],
            ['name' => 'Orchidée Phalaenopsis', 'cat' => 'fleur', 'img' => 'fleur3.png', 'price' => 28],
            ['name' => 'Cactus Cierge', 'cat' => 'grass', 'img' => 'grass1.png', 'price' => 15],
            ['name' => 'Aloe Vera Barbadensis', 'cat' => 'grass', 'img' => 'grass2.jpg', 'price' => 10],
            ['name' => 'Echeveria Elegans', 'cat' => 'grass', 'img' => 'grass3.jpg', 'price' => 8],
            ['name' => 'Basilic Grand Vert', 'cat' => 'arom', 'img' => 'arom1.jpg', 'price' => 7],
            ['name' => 'Menthe Poivrée', 'cat' => 'arom', 'img' => 'arom2.jpg', 'price' => 6],
            ['name' => 'Romarin Officinal', 'cat' => 'arom', 'img' => 'arom3.jpg', 'price' => 9],
        ];

        // Création des plantes
        $plants = [];
        foreach ($plantData as $data) {
            $plant = new Plant();
            $plant->setName($data['name']);
            $plant->setDescription('Une magnifique ' . $data['name'] . ' pour embellir votre quotidien.');
            $plant->setPrice($data['price']);
            $plant->setCategory($categories[$data['cat']]);
            $plant->setImageUrl('/src/assets/img/' . $data['img']);
            $plant->setOwner($admin);
            $this->entityManager->persist($plant);
            $plants[] = $plant;
        }
        // Enregistrement des plantes dans la base de données
        $this->entityManager->flush();
        $io->info(count($plants) . ' plants created.');

        $io->section('Creating Orders and OrderDetails...');
        foreach ($users as $currentUser) {
            // Créer une commande directement pour chaque utilisateur
            $order = new Order();
            $order->setClient($currentUser);
            
            $totalPrice = 0;
            $plantSummary = [];
            $randomPlants = (array)array_rand($plants, rand(1, 3));

            foreach ($randomPlants as $plantIndex) {
                $plantToAdd = $plants[$plantIndex];
                $quantity = rand(1, 3);
                
                $order->addPlant($plantToAdd);
                
                $totalPrice += $plantToAdd->getPrice() * $quantity;
                $plantSummary[] = [
                    'name' => $plantToAdd->getName(),
                    'quantity' => $quantity,
                    'price' => $plantToAdd->getPrice(),
                ];
            }
            $this->entityManager->persist($order);

            // Créer les détails de la commande
            $orderDetails = new OrderDetails();

            // On récupère la commande créée
            $orderDetails->setTheOrder($order);

            // On set les détails de la commande
            $orderDetails->setClientFirstName($currentUser->getFirstName());
            $orderDetails->setClientLastName($currentUser->getLastName());
            $orderDetails->setClientEmail($currentUser->getEmail());
            $orderDetails->setClientAddress($currentUser->getAddress());
            $orderDetails->setClientPhoneNumber($currentUser->getPhoneNumber());
            $orderDetails->setOrderDate(new \DateTimeImmutable());
            $orderDetails->setTotalPrice((string)$totalPrice);

            // On récupère le contenu du panier
            $orderDetails->setPlantSummary($plantSummary);
            $this->entityManager->persist($orderDetails);

            $io->text('Created order and details for ' . $currentUser->getEmail());
        }

        $this->entityManager->flush();
        $io->info('Orders and OrderDetails created.');

        $io->success('Database seeding complete!');

        return Command::SUCCESS;
    }

    /**
     * @throws Exception
     */
    private function truncateAllTables(SymfonyStyle $io): void
    {
        $connection = $this->entityManager->getConnection();
        $io->info('Truncating all tables (PostgreSQL method)...');

        // Suppression des données existantes dans les tables
        $connection->executeStatement('TRUNCATE TABLE "order_details", "order_plant", "order", "plant", "category",
         "user" RESTART IDENTITY CASCADE');
    }
}
