<?php

namespace App\Command;

use App\Document\Review;
use App\Entity\Category;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\Plant;
use App\Entity\User;
use Doctrine\ODM\MongoDB\DocumentManager;
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
    private DocumentManager $documentManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, DocumentManager $documentManager,
                                UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->documentManager = $documentManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seeding Database...');

        $this->truncateAllTables($io);

        // Création des catégories
        $io->section('Creating Categories...');
        $categoryNames = [
            'Plantes d\'intérieur' => 'int', 'Plantes d\'extérieur' => 'ext', 'Fleurs' => 'fleur',
            'Plantes grasses' => 'grass', 'Plantes aromatiques' => 'arom',
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

        // Création des utilisateurs (admin et user)
        $io->section('Creating Users...');
        $users = [];
        $admin = new User();
        $admin->setEmail('admin@plantoshop.com')->setFirstName('Admin')->setLastName('User')
              ->setRoles(['ROLE_ADMIN'])
              ->setPassword($this->passwordHasher->hashPassword($admin, 'adminpass'));
        $this->entityManager->persist($admin);
        $users[] = $admin;

        $user = new User();
        $user->setEmail('user@plantoshop.com')->setFirstName('Regular')->setLastName('User')
             ->setAddress('123 Green St, Plant City')
             ->setPassword($this->passwordHasher->hashPassword($user, 'userpass'));
        $this->entityManager->persist($user);
        $users[] = $user;
        $this->entityManager->flush();
        $io->info(count($users) . ' users created.');

        // Création des plantes
        $io->section('Creating Plants...');
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
        $plants = [];
        foreach ($plantData as $data) {
            $plant = new Plant();
            $plant->setName($data['name'])
                  ->setDescription('Une magnifique ' . $data['name'] . ' pour embellir votre quotidien.')
                  ->setPrice($data['price'])->setCategory($categories[$data['cat']])
                  ->setImageUrl('/src/assets/img/' . $data['img'])->setOwner($admin);
            $this->entityManager->persist($plant);
            $plants[] = $plant;
        }
        $this->entityManager->flush();
        $io->info(count($plants) . ' plants created.');

        // Création des commandes aléatoires pour les utilisateurs
        $io->section('Creating Orders and OrderDetails...');
        foreach ($users as $currentUser) {
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
                $plantSummary[] = ['name' => $plantToAdd->getName(), 'quantity' => $quantity, 'price' => $plantToAdd->getPrice()];
            }
            $this->entityManager->persist($order);

            $orderDetails = new OrderDetails();
            $orderDetails->setTheOrder($order)->setClientFirstName($currentUser->getFirstName())
                         ->setClientLastName($currentUser->getLastName())->setClientEmail($currentUser->getEmail())
                         ->setClientAddress($currentUser->getAddress())->setClientPhoneNumber($currentUser->getPhoneNumber())
                         ->setTotalPrice((string)$totalPrice)->setPlantSummary($plantSummary);
            $this->entityManager->persist($orderDetails);
        }
        $this->entityManager->flush();
        $io->info('Orders and OrderDetails created.');

        // Création des avis aléatoires pour l'utilisateur connecté
        $io->section('Creating Reviews...');
        $reviewComments = [
            'Absolument magnifique, je recommande !', 'Très facile d\'entretien, parfaite pour les débutants.',
            'Arrivée en parfait état, très bien emballée.', 'Un peu plus petite que ce que j\'imaginais, mais très jolie.',
            'Les couleurs sont superbes, je suis ravi(e) !',
        ];
        if ($user) {
            for ($i = 0; $i < 5; $i++) {
                $review = new Review();
                $randomPlant = $plants[array_rand($plants)];
                $review->setPlantId($randomPlant->getId())->setUserId($user->getId())->setUsername($user->getFirstName())
                       ->setRating(rand(4, 5))->setComment($reviewComments[array_rand($reviewComments)]);
                $this->documentManager->persist($review);
            }
            $this->documentManager->flush();
            $io->info('5 reviews created.');
        }

        $io->success('Database seeding complete!');
        return Command::SUCCESS;
    }

    /*
     * Supprime toutes les tables de la base de données.
     */
    private function truncateAllTables(SymfonyStyle $io): void
    {
        // Pour PostgreSQL
        $connection = $this->entityManager->getConnection();
        $io->info('Truncating SQL tables (PostgreSQL method)...');
        $connection->executeStatement('TRUNCATE TABLE "order_details", "order_plant", "order", "plant", "category",
         "user" RESTART IDENTITY CASCADE');

        // Pour MongoDB
        $io->info('Dropping reviews collection (MongoDB)...');
        $schemaManager = $this->documentManager->getSchemaManager();
        $schemaManager->dropCollections();
        $schemaManager->createCollections();
    }
}
