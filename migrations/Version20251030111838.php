<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251030111838 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cart (id SERIAL NOT NULL, owner_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BA388B77E3C61F9 ON cart (owner_id)');
        $this->addSql('CREATE TABLE cart_plant (cart_id INT NOT NULL, plant_id INT NOT NULL, PRIMARY KEY(cart_id, plant_id))');
        $this->addSql('CREATE INDEX IDX_F4E9607A1AD5CDBF ON cart_plant (cart_id)');
        $this->addSql('CREATE INDEX IDX_F4E9607A1D935652 ON cart_plant (plant_id)');
        $this->addSql('CREATE TABLE category (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "order" (id SERIAL NOT NULL, client_id INT NOT NULL, cart_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F529939819EB6921 ON "order" (client_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F52993981AD5CDBF ON "order" (cart_id)');
        $this->addSql('CREATE TABLE order_plant (order_id INT NOT NULL, plant_id INT NOT NULL, PRIMARY KEY(order_id, plant_id))');
        $this->addSql('CREATE INDEX IDX_289D798F8D9F6D38 ON order_plant (order_id)');
        $this->addSql('CREATE INDEX IDX_289D798F1D935652 ON order_plant (plant_id)');
        $this->addSql('CREATE TABLE order_details (id SERIAL NOT NULL, order_id INT NOT NULL, client_first_name VARCHAR(255) NOT NULL, client_last_name VARCHAR(255) NOT NULL, client_email VARCHAR(255) NOT NULL, client_address VARCHAR(255) DEFAULT NULL, client_phone_number VARCHAR(20) DEFAULT NULL, order_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, total_price NUMERIC(10, 2) NOT NULL, plant_summary JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_845CA2C18D9F6D38 ON order_details (order_id)');
        $this->addSql('COMMENT ON COLUMN order_details.order_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE plant (id SERIAL NOT NULL, category_id INT NOT NULL, owner_id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, price INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AB030D7212469DE2 ON plant (category_id)');
        $this->addSql('CREATE INDEX IDX_AB030D727E3C61F9 ON plant (owner_id)');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B77E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cart_plant ADD CONSTRAINT FK_F4E9607A1AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cart_plant ADD CONSTRAINT FK_F4E9607A1D935652 FOREIGN KEY (plant_id) REFERENCES plant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F529939819EB6921 FOREIGN KEY (client_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F52993981AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_plant ADD CONSTRAINT FK_289D798F8D9F6D38 FOREIGN KEY (order_id) REFERENCES "order" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_plant ADD CONSTRAINT FK_289D798F1D935652 FOREIGN KEY (plant_id) REFERENCES plant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_details ADD CONSTRAINT FK_845CA2C18D9F6D38 FOREIGN KEY (order_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE plant ADD CONSTRAINT FK_AB030D7212469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE plant ADD CONSTRAINT FK_AB030D727E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" DROP contact');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE cart DROP CONSTRAINT FK_BA388B77E3C61F9');
        $this->addSql('ALTER TABLE cart_plant DROP CONSTRAINT FK_F4E9607A1AD5CDBF');
        $this->addSql('ALTER TABLE cart_plant DROP CONSTRAINT FK_F4E9607A1D935652');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F529939819EB6921');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F52993981AD5CDBF');
        $this->addSql('ALTER TABLE order_plant DROP CONSTRAINT FK_289D798F8D9F6D38');
        $this->addSql('ALTER TABLE order_plant DROP CONSTRAINT FK_289D798F1D935652');
        $this->addSql('ALTER TABLE order_details DROP CONSTRAINT FK_845CA2C18D9F6D38');
        $this->addSql('ALTER TABLE plant DROP CONSTRAINT FK_AB030D7212469DE2');
        $this->addSql('ALTER TABLE plant DROP CONSTRAINT FK_AB030D727E3C61F9');
        $this->addSql('DROP TABLE cart');
        $this->addSql('DROP TABLE cart_plant');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('DROP TABLE order_plant');
        $this->addSql('DROP TABLE order_details');
        $this->addSql('DROP TABLE plant');
        $this->addSql('ALTER TABLE "user" ADD contact VARCHAR(255) DEFAULT NULL');
    }
}
