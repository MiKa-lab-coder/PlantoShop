<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Schéma initial complet — crée toutes les tables du schéma final.
 * Remplace l'ancienne migration delta (qui supposait des tables déjà existantes).
 */
final class Version20251107130827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création du schéma complet (user, category, plant, order, order_plant, order_details)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE "user" (
            id SERIAL NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            address VARCHAR(255) DEFAULT NULL,
            phone_number VARCHAR(20) DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');

        $this->addSql('CREATE TABLE category (
            id SERIAL NOT NULL,
            name VARCHAR(255) NOT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE TABLE plant (
            id SERIAL NOT NULL,
            category_id INT NOT NULL,
            owner_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            price INT NOT NULL,
            image_url VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_AB030D2712469DE2 ON plant (category_id)');
        $this->addSql('CREATE INDEX IDX_AB030D277E3C61F9 ON plant (owner_id)');
        $this->addSql('ALTER TABLE plant ADD CONSTRAINT FK_AB030D2712469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE plant ADD CONSTRAINT FK_AB030D277E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE "order" (
            id SERIAL NOT NULL,
            client_id INT NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_F529939819EB6921 ON "order" (client_id)');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F529939819EB6921 FOREIGN KEY (client_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE order_plant (
            order_id INT NOT NULL,
            plant_id INT NOT NULL,
            PRIMARY KEY(order_id, plant_id)
        )');
        $this->addSql('CREATE INDEX IDX_order_plant_order ON order_plant (order_id)');
        $this->addSql('CREATE INDEX IDX_order_plant_plant ON order_plant (plant_id)');
        $this->addSql('ALTER TABLE order_plant ADD CONSTRAINT FK_order_plant_order FOREIGN KEY (order_id) REFERENCES "order" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_plant ADD CONSTRAINT FK_order_plant_plant FOREIGN KEY (plant_id) REFERENCES plant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE order_details (
            id SERIAL NOT NULL,
            the_order_id INT NOT NULL,
            client_first_name VARCHAR(255) NOT NULL,
            client_last_name VARCHAR(255) NOT NULL,
            client_email VARCHAR(255) NOT NULL,
            client_address VARCHAR(255) DEFAULT NULL,
            client_phone_number VARCHAR(20) DEFAULT NULL,
            order_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            total_price NUMERIC(10, 2) NOT NULL,
            plant_summary JSON DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_845CA2C1C416F85B ON order_details (the_order_id)');
        $this->addSql('ALTER TABLE order_details ADD CONSTRAINT FK_845CA2C1C416F85B FOREIGN KEY (the_order_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE order_details DROP CONSTRAINT FK_845CA2C1C416F85B');
        $this->addSql('ALTER TABLE order_plant DROP CONSTRAINT FK_order_plant_order');
        $this->addSql('ALTER TABLE order_plant DROP CONSTRAINT FK_order_plant_plant');
        $this->addSql('ALTER TABLE plant DROP CONSTRAINT FK_AB030D2712469DE2');
        $this->addSql('ALTER TABLE plant DROP CONSTRAINT FK_AB030D277E3C61F9');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F529939819EB6921');
        $this->addSql('DROP TABLE order_details');
        $this->addSql('DROP TABLE order_plant');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('DROP TABLE plant');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE "user"');
    }
}
